/**
 * Created by kostet on 03.08.2017.
 */

export default class BaseDiscussion {
    constructor(params) {
        this.params = params;
        this.sending_files = [];

        this.last_item = null;
        this.last_discussion_item = null;
        this.filter = {};
        this.last_panel = undefined;
        this.posting = false;
        this.msg_field = '';
        this.default_object_id = 0;

        this.message_field = null;
        this.message_ask_field = null;

        this.dealer_id = 0;

        this.reply_on_message_id = 0;

        this.sidebar_panel_hide_interval = null;
        this.scroll_container = 'container-discussion-messages';
    }

    start() {
        //Фильтр списка сообщение
        $(document).on('click', this.params.filter_visibility_messages, $.proxy(this.onFilterDiscussionsList, this));

        //Загрузка списка сообщение выбранной дискуссии
        $(document).on('click', this.params.on_show_discussion_messages, $.proxy(this.onLoadDiscussionMessagesByDiscussion, this));

        //Отображение / скрытие панелий при наведении курсора
        $(document).on('mouseover', this.params.container_discussion_panel, $.proxy(this.onMouseMoveInDiscussionContainer, this));

        //Отправка обычного сообщения
        $(document).on('click', this.params.frm_discussion_send_button, $.proxy(this.onSendMessage, this));

        //Отправка сообщение с вопросом
        $(document).on('click', this.params.frm_discussion_send_ask_button, $.proxy(this.onSendAskMessage, this));

        //Работа с правой панелью сообщений
        $(document).on('mousemove', '#sidebar-wrapper', $.proxy(this.onShowSidebarPanel, this));
        $(document).on('mouseout', '#sidebar-wrapper', $.proxy(this.onHideSidebarPanel, this));

        //Открытие панели сообщений по клику
        $(document).on('click', '#js-chat-messages-counter', $.proxy(this.onShowSidebarPanel, this));

        //Цитирование сообщения
        $(document).on('click', '.js-reply-on-comment', $.proxy(this.onReplyMessage, this));

        //Отобразить данные по дискуссии
        $(document).on('click', '.discussion-chat-item-in-sidebar', $.proxy(this.onShowDiscussionDataByChatItem, this));

        this.initEditors();
    }

    /**
     * Отобразить информацию о дискусси по
     * @param event
     */
    onShowDiscussionDataByChatItem(event) {
        let element = $(event.target);

        if (element.data('discussion-id') == undefined) {
            element = element.parent();
        }

        //Принудильный клик для загрузки последних сообщений
        if ($('.discussion__dealer__item__' + element.data('dealer-id')).length != 0) {
            $('.discussion__dealer__item__' + element.data('dealer-id')).trigger('click');

            //Скроллим до небходимого итема
            scrollTop('.discussion__dealer__item__' + element.data('dealer-id'), 'dealers-discussions-container');

            //Проверка на тип сообщений
            //Если сообщение пришло с раздела Задачать вопрос делаем проверку на активную панель и дальшеесли она неактивна
            if (element.data('message-type') == 'ask') {
                let discussion_type = $('.discussions-messages-types');

                $.each(discussion_type, (index, item) => {
                    let element = $(item);

                    if (!element.hasClass('current') && element.data('type') == 'ask') {
                        element.trigger('click');
                    }
                });
            } else {
                //Тайм аут для загрузки списка сообщений
                let attemts_count = 0; //Количество попыток
                let item_interval = setInterval(() => {
                    if ($('.discussion__list__item__' + element.data('discussion-id')).length > 0 || attemts_count < 5) {
                        $('.discussion__list__item__' + element.data('discussion-id')).trigger('click');

                        clearInterval(item_interval);
                    }

                    attemts_count++;
                }, 1000);
            }

        } else {
            $('.discussion__list__item__' + element.data('discussion-id')).trigger('click');
        }
    }

    onShowSidebarPanel(event) {
        let element = $(event.target);

        if (element.data('open') == undefined) {
            element = element.parent();
        }

        if (element.data('open') != undefined) {
            $("#discussion-wrapper").toggleClass('toggled');

            element.css('right', '-230px');

            //Обнуляем количетво сообщений от пользователей
            this.getDiscussionChatBySidebarCounter().text(0);
        }

        clearInterval(this.sidebar_panel_hide_interval);
    }

    onHideSidebarPanel() {
        this.sidebar_panel_hide_interval = setInterval(() => {
            $("#discussion-wrapper").toggleClass('toggled');

            $('#js-chat-messages-counter').css('right', '0px');

            clearInterval(this.sidebar_panel_hide_interval);
        }, 500);
    }

    /**
     * Инициалицзация текстовых полей для сообщений
     */
    initEditors() {
        if (this.getFrmDiscussionMessageElement()) {
            this.getFrmDiscussionMessageElement().html('');

            if (this.message_field == null) {
                this.message_field = new Medium({
                    element: document.getElementById(this.params.frm_discussion_message_element),
                    mode: Medium.richMode,
                    placeholder: 'Введите комментарий',
                    attributes: null,
                    autofocus: true,
                    autoHR: false,
                    tags: null
                });
            }
        }

        if (this.getFrmDiscussionAskMessageElement()) {
            this.getFrmDiscussionAskMessageElement().html('');

            if (this.message_ask_field == null) {
                this.message_ask_field = new Medium({
                    element: document.getElementById(this.params.frm_discussion_ask_message_element),
                    mode: Medium.richMode,
                    placeholder: 'Введите комментарий',
                    attributes: null,
                    tags: null
                });
            }
        }
    }

    /**
     * Цитировать сообщение
     */
    onReplyMessage(event) {
        let $element = $(event.target), message_type = $element.data('message-type'),
            message_id = $element.data('message-id'), element_msg = $element.data('msg'),
            text_element = $('[data-message-field=' + message_type + ']');

        $element.hide();
        if (text_element != undefined) {
            let element_id = (message_type == 'message' ? this.params.frm_discussion_message_element : this.params.frm_discussion_ask_message_element),
                text_area = $('#' + element_id);

            text_area.focus();

            text_area.html('');
            text_area.append('<blockquote>' + element_msg + '</blockquote><br/>');

            this.reply_on_message_id = message_id;

            this.placeCaretAtEnd(document.getElementById(element_id));
        }
    }

    onSendMessage(event) {
        event.preventDefault();

        this.msg_field = this.params.frm_discussion_message_element;
        this.sendMessage(this.params.on_post_new_message_url, this.params.discussion_model_messages, 'message', (result) => {
            if (result.success) {
                this.loadDiscussionMessages();
            }
        });
    }

    onSendAskMessage(event) {
        event.preventDefault();

        this.msg_field = this.params.frm_discussion_ask_message_element;
        this.sendMessage(this.params.on_post_new_message_url, this.params.discussion_ask, 'ask', (result) => {
            if (result.success) {
                this.loadAskMessages();
            }
        });
    }

    sendMessage(url, uploader, message_type, callback) {
        let total_uploaded_files = 0;

        this.setDefaultDiscussionItem();
        for (var i in this.getFilesParam(uploader)) {
            if (this.getFilesParam(uploader).hasOwnProperty(i)) {
                total_uploaded_files++;
            }
        }

        if (this.posting || ($.trim(this.getMessage()) == '' && total_uploaded_files == 0)) {
            showAlertPopup('Ошибка.', 'Введите сообщение или загрузите файл.');
            return;
        }

        this.message_type = message_type;
        let data = $.extend(this.getFilesParam(), {
                id: this.last_discussion_item.data('discussion-id'),
                model_id: this.last_discussion_item.data('model-id'),
                dealer_id: this.getDealerId(),
                message: this.getMessage(),
                reply_on_message_id: this.reply_on_message_id,
                message_type: message_type,
            }
        );

        data = $.extend(this.getMessageDirection(message_type), data);

        this.posting = true;
        new Promise((resolve, reject) => {
            $.post(url,
                data,
                (result) => {
                    resolve(result);
                    this.posting = false;
                })
        }).then((result) => {
            this.getMessageField().html('');
            this.deleteSentFiles(uploader);

            if (result.response != undefined) {
                data = $.extend(result.response, data);

                this.makeRequestData(data);
            }

            if (callback != undefined) {
                callback(result);
            }
        });
    }

    loadAskMessages() {
        new Promise((resolve, reject) => {
            $.post(this.params.on_load_ask_messages_url,
                {
                    id: this.last_discussion_item.data('discussion-id'),
                    model_id: this.last_discussion_item.data('model-id'),
                    dealer_id: this.getDealerId(),
                    reply_on_message_id: this.reply_on_message_id,
                    message_type: this.message_type,
                },
                (result) => {
                    if (result.success) {
                        resolve(result);
                    } else {
                        reject(result);
                    }
                });
        }).then((result) => {
            this.getDiscussionAskMessagesContainer().html(result.messages_list);
        }).catch((result) => {

        });
    }

    getMessage() {
        return this.getMessageField().html();
    }

    getMessageField() {
        return $('#' + this.msg_field);
    }

    /**
     * Загрузка списка сообщений по выбранной дискуссии (заявки)
     * @param event
     */
    onLoadDiscussionMessagesByDiscussion(event) {
        let discussion_item = $(event.target);

        if (discussion_item.data('model-id') == undefined) {
            discussion_item = discussion_item.parent();
        }

        this.params.model_id = discussion_item.data('model-id');
        this.last_discussion_item = discussion_item;

        this.resetDiscussionItems();
        this.loadDiscussionMessages();
    }

    /**
     * Загрузка списка сообщений
     */
    loadDiscussionMessages() {
        this.initEditors();

        this.setDefaultDiscussionItem();
        this.last_discussion_item.addClass('active');

        new Promise((resolve, reject) => {
            Pace.start();

            $.post(this.params.on_load_messages_list_by_model_url, {
                model_id: this.params.model_id != undefined ? this.params.model_id : this.params.default_model_id,
                dealer_id: this.params.dealer_id != undefined ? this.params.dealer_id : '',
                messages_status: this.params.messages_status,
                messages_type: this.params.messages_type,
                filter_by_user_id: this.params.last_user_id
            }, (result) => {
                if (result.success) {
                    resolve(result);
                } else {
                    reject(result);
                }
            })
        }).then((result) => {
            //По умолчанию сделам первую из списка дискуссию выбранной
            this.setDefaultDiscussionItem();

            if (result.messages_list.length == 0) {
                this.getModelMessagesContainer().html('Нет сообщений');
            } else {
                this.getModelMessagesContainer().html(result.messages_list);
                this.getUnreadMessagesCount();
            }

            this.scrollDown(this.scroll_container);

            Pace.stop();
        }).catch((result) => {
            this.getModelMessagesContainer().html('Ошибка при получении списка сообщений.');

            Pace.stop();
        });
    }

    /**
     * Отображение списка дискуссий (Все или только непрочитанные)
     * @param event
     */
    onFilterDiscussionsList(event) {
        let $element = $(event.target);

        this.getDiscussionVisibilityButtons().removeClass('current');
        $element.addClass('current');

        if ($element.data('show') == 'unread') {
            this.getDiscussionListItem().hide();
            $('[data-unread-count]').show();
        } else {
            this.getDiscussionListItem().show();
        }
    }

    getUnreadMessagesCount() {
        let discussion_ids = [];

        $('.discussion__list__item', this.getDiscussionsContainer()).each((ind, el) => {
            let discussion_item = $(el);

            if (discussion_item.attr('data-unread') == undefined) {
                discussion_ids.push(discussion_item.data('discussion-id'));
            } else {
                return;
            }
        });

        if (discussion_ids.length > 0) {
            new Promise((resolve2, reject2) => {
                $.post(this.params.on_load_discussion_unread_url, {
                    discussion_ids: discussion_ids.join(':')
                }, (result) => {
                    resolve2(result);
                });
            }).then((result) => {
                this.drawUnreadDiscussionData(result);
            });
        }
    }

    drawUnreadDiscussionData(result) {
        $.each(result.unread_count, (index, value) => {
            $('.discussion__list__item__' + index, this.getDiscussionsContainer()).attr('data-unread', true);
            if (value > 0) {
                $('.discussion__list__item__' + index, this.getDiscussionsContainer()).attr('data-unread-count', value);
            }
        });
    }

    /**
     * Видимость панелей при движении курсора
     * @param event
     */
    onMouseMoveInDiscussionContainer(event) {
        let panel = $(event.target).closest(this.params.container_discussion_panel);

        if (this.last_panel != panel) {
            this.last_panel = panel;

            $(this.params.container_discussion_panel).removeClass('animate-show-panel');
            $(this.params.container_discussion_panel).addClass('animate-hide-panel')

            this.last_panel.removeClass('animate-hide-panel').addClass('animate-show-panel');
        }
    }

    addDiscussionLastItemClass(cls) {
        if (this.last_discussion_item != null) {
            this.last_discussion_item.addClass(cls);
        }
    }

    getDiscussionVisibilityButtons() {
        return $(this.params.filter_visibility_messages);
    }

    getDiscussionsContainer() {
        return undefined;
    }

    getModelMessagesContainer() {
        return $(this.params.model_messages_container);
    }

    getDiscussionAskMessagesContainer() {
        return $(this.params.discussion_ask_messages_container);
    }

    getElShowDiscussionMessages() {
        return $(this.params.on_show_discussion_messages);
    }

    resetDiscussionItems() {
        $(this.params.on_show_discussion_messages).removeClass('active');
    }

    getElementByClass(element, cls) {
        let current_item = null;

        $(element).each((ind, item) => {
            if ($(item).hasClass(cls)) {
                current_item = $(item);
            }
        });

        return current_item;
    }

    getDiscussionItemParent() {
        return $('#dealer-discussions-container');
    }

    getDiscussionListItem() {
        return $('.discussion__list__item', this.getDiscussionItemParent());
    }

    getDiscussionModelsList() {
        return $('.js-dealers-discussion-list-item');
    }

    getFrmDiscussionMessageElement() {
        if (this.params.frm_discussion_message_element != undefined) {
            return $('#' + this.params.frm_discussion_message_element);
        }

        return undefined;
    }

    getFrmDiscussionAskMessageElement() {
        if (this.params.frm_discussion_ask_message_element != undefined) {
            return $('#' + this.params.frm_discussion_ask_message_element);
        }

        return undefined;
    }

    setDefaultDiscussionItem() {
        if (this.last_discussion_item == null) {
            this.last_discussion_item = $('.discussion__list__item', this.getDiscussionsContainer()).eq(0);

            this.addDiscussionLastItemClass('active');
        }
    }

    /**
     *
     * @param message_type
     * @returns {{}}
     */
    getMessageDirection(message_type) {
        return {};
    }

    makeRequestData(data) {
        alert('Not implemented');
    }

    /**
     * Получить обьект ws
     * @returns {WebSocket}
     */
    getWebSocket() {
        return this.params.websockets;
    }

    placeCaretAtEnd(el) {
        el.focus();
        if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
            var range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (typeof document.body.createTextRange != "undefined") {
            var textRange = document.body.createTextRange();
            textRange.moveToElementText(el);
            textRange.collapse(false);
            textRange.select();
        }
    }

    getFilesParam(uploader) {
        var params = {}

        if (this.sending_files.length == 0 && uploader) {
            this.sending_files = uploader.getFilesUploadList();
        }

        for (var i = 0; i < this.sending_files.length; i++) {
            params['files[' + i + ']'] = this.sending_files[i];
        }

        return params;
    }

    deleteSentFiles(uploader) {
        if (uploader) {
            if (uploader != null) {
                uploader.reset();
                uploader.drawFiles();
            }

            this.sending_files = [];
        }
    }

    error(title, msg) {
        //this.showMessage('error', msg);
        showAlertPopup(title, msg);
    }

    info(msg) {
        this.showMessage('info', msg);
    }

    success(msg) {
        this.showMessage('success', msg);
    }

    warning(msg) {
        this.showMessage('warning', msg);
    }

    showMessage(type, msg) {

    }

    /**
     *
     * @returns {*|jQuery|HTMLElement}
     */
    getDiscussionChatBySidebarPanel() {
        return $('#discussion-rightbar-chat');
    }

    /**
     * Контейнер подсчета новых сообщений
     */
    getDiscussionChatBySidebarCounter() {
        return $('.container-messages-counter');
    }

    /**
     *
     * @param data
     * @returns {string}
     */
    formatSidebarChatItem(data) {
        return `
            <li class="discussion-chat-item-in-sidebar" 
                data-discussion-id="${data.discussion_id}" 
                data-model-id="${data.model_id}" 
                data-dealer-id="${data.dealer_id}" 
                data-message-type="${data.message_type}"
            >
                <span class="model">№ ${data.model_id}</span>
                <span class="author">${data.send_user}</span>
                <span>${data.message}</span>
                <span class="time">${data.message_time}</span>
            </li>
        `;
    }

    reloadAskMessages() {

    }

    /**
     * Определяем номер дилера
     * @returns {number}
     */
    getDealerId() {
        let selected_dealer_item = this.getElementByClass('.dealer_model_item', 'active'), dealer_id = 0;
        if (selected_dealer_item != undefined) {
            dealer_id = selected_dealer_item.data('dealer-id');
        } else if ($('.js-dealer-discussion-list-item').eq(0).length > 0) {
            dealer_id = $('.js-dealer-discussion-list-item').eq(0).data('dealer-id');
        }

        return dealer_id;
    }

    /**
     * Скороллинг до последнего сообщения
     * @param element
     */
    scrollDown(element) {
        let height = document.getElementById(element).scrollHeight;

        $('#' + element).animate({
                scrollTop: height + "px"
            },
            {duration: 500});

    }
}
