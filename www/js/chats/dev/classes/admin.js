/**
 * Created by kostet on 03.08.2017.
 */
//import DiscussionOnlineChatBase from "./base";
import DiscussionWebSockets from "./websockets";

export default class Admin extends DiscussionWebSockets {
    constructor(params) {
        super(params);

        this.params.messages_status = 'unread';
        this.params.messages_type = '';

        this.params.last_user_id = null;
        this.params.dealer_id = 0;
        this.params.discussions_ids = [];
    }

    start() {
        super.start();

        //Загрузка списка дискуссий по дилеру
        $(document).on('click', this.params.on_dealer_show_discussions, $.proxy(this.onShowDealerDiscussions, this));

        //Переключение панелей (Типы сообщение - Все сообщения / Только Общие)
        $(document).on('click', this.params.on_switch_discussions_messages_type, $.proxy(this.onSwitchDiscussionsMessagesTypes, this));

        //Вывод списка сообщений (Все сообщения по заявке или только непрочитанные)
        $(document).on('click', this.params.on_switch_discussions_messages_status, $.proxy(this.onSwitchDiscussionsMessagesStatuses, this));

        //Фильтр по названию дилера / пользователя и номера дилера
        $(document).on('keyup', '#txt_discussions_filter_by_name', $.proxy(this.onFilterDiscussionsByName, this));

        $(document).on('click', '.js-sort-dealers', $.proxy(this.onMakeDealersSort, this));

        this.initDefaultValues();
        this.getUnreadMessagesCount();

        return this;
    }

    initDefaultValues() {
        this.params.last_user_id = this.getDealersItemsList().eq(0).data('user-id');

        //Загружаем количество непрочитанных сообщений для заявок
        this.loadUnreadDataForModels();

        this.scrollDown(this.scroll_container);
    }

    loadUnreadDataForModels() {
        var models_ids = $('.discussion__list__item', this.getDealerContainerDiscussions()).map((ind, item) => {
            return $(item).data("model-id");
        });

        new Promise((resolve, reject) => {
            Pace.start();

            $.post(this.params.on_load_discussion_unread_models_url, {
                models_ids: models_ids.toArray()
            }, (result) => {
                if (result.success) {
                    resolve(result);
                } else {
                    reject(result);
                }
                Pace.stop();
            });
        }).then((result) => {
            $.each(result.unread_count, (i, count) => {
                var unread_messages_count = parseInt(count);

                if (unread_messages_count != 0) {
                    $('[data-model-id=' + i + ']', this.getDealerContainerDiscussions()).append("<i style='top: 90%; right: 3px; min-width: 0px; height: 9px;'>&nbsp;</i>");
                }
            });
        }).catch((result) => {
            this.error(result.title, result.msg);

            Pace.stop();
        });
    }

    //Переключение панелей (Типы сообщение - Все сообщения / Только Общие)
    onSwitchDiscussionsMessagesTypes(event) {
        this.onSwitchPanel(event, this.params.on_switch_discussions_messages_type, (item) => {
            this.params.messages_type = item.data('type');
            this.params.messages_status = 'all';

            this.resetDiscussionMessagesStatus();
            this.switchPanelsByType();

            let selected_item = this.getElementByClass('.dealer_model_item', 'active'),
                selected_model_item = this.getElementByClass(this.params.on_show_discussion_messages, 'active');

            if (selected_item != null) {
                this.params.dealer_id = selected_item.data('dealer-id');
                this.params.model_id = selected_model_item.data('model-id');

                this.loadDiscussionAskMessages();
            }
        });
    }

    //Загружаем список сообщение для выбранного дилера, только Общие сообщения
    loadDiscussionAskMessages() {
        if (this.params.messages_type == 'ask') {
            this.params.model_messages_container = '#container-discussion-messages-ask';
        } else {
            this.params.model_messages_container = '#container-discussion-messages';
        }

        this.loadDiscussionMessages();
    }

    //Переопределяем метод для отправки сообщение только выбранному дилеру
    loadAskMessages() {
        this.loadDiscussionAskMessages();
    }

    onSwitchDiscussionsMessagesStatuses(event) {
        this.onSwitchPanel(event, this.params.on_switch_discussions_messages_status, (item) => {
            this.params.messages_status = item.data('type');

            let selected_item = this.getElementByClass(this.params.on_show_discussion_messages, 'active');
            if (selected_item != null) {
                this.params.model_id = selected_item.data('model-id');

                this.loadDiscussionMessages();
            }
        });
    }

    onSwitchPanel(event, element, callback) {
        let item = $(event.target);

        if (!item.hasClass('current')) {
            $(element).removeClass('current');
            item.addClass('current');

            if (callback != undefined) {
                callback(item);
            }
        }
    }

    /**
     * Фильтр списка дискуссий
     * @param event
     */
    onFilterDiscussionsList(event) {
        super.onFilterDiscussionsList(event);

        $('#txt_discussions_filter_by_name').val('');
    }

    onShowDealerDiscussions(event) {
        let item = $(event.target);

        if (item.data('user-id') == undefined) {
            item = item.parent();
        }

        //Если при загрузке списка сообщение панель переключена на Непрочитанные, автоматически переводим панель в обычный статус
        /*if (this.params.messages_status == 'unread' ) {
         this.getDiscussionMessagesStatusSwitch().removeClass('current').eq(0).addClass('current');
         }*/
        //По умолчанию открываем понель с непрочитанными сообщениями
        this.getDiscussionMessagesStatusSwitch().removeClass('current').eq(1).addClass('current');

        //Принудительно переводим на панель заявок
        if (this.params.messages_type == 'ask') {
            $('[data-type="all"]').trigger('click');
        }

        this.params.last_user_id = item.data('user-id');
        this.params.dealer_id = item.data('dealer-id');

        this.getDealersItemsList().removeClass('active');
        item.addClass('active');

        new Promise((resolve, reject) => {
            Pace.start();

            $.post(this.params.on_load_dealer_discussions_url, {
                filter_by_user: item.data('user-id'),
                filter_by_dealer: this.params.dealer_id,
                messages_type: "unread",

            }, (result) => {
                if (result.success) {
                    resolve(result)
                } else {
                    reject(result);
                }
                Pace.stop();
            });
        }).then((result) => {
            this.getDealerContainerDiscussions().html(result.dealer_discussions);
            this.getDealerContainerDiscussionsMessages().html(result.unread_messages_list);
            this.getDealerContainerDiscussionsAskMessages().html(result.dealer_discussion_ask_messages_list);

            this.setDefaultDiscussionItem(true);

            this.scrollDown(this.scroll_container);

            //Загружаем количество непрочитанных сообщений для заявок
            this.loadUnreadDataForModels();
        }).catch((result) => {
            this.error(result.title, result.msg);

            Pace.stop();
        });
    }

    getDiscussionsContainer() {
        return $('#dealers-discussions-container');
    }

    /**
     * Установить по умолчанию выбранную дискуссию по заявке
     * @param forcibly_set
     *
     */
    setDefaultDiscussionItem(forcibly_set = false) {
        if (this.last_discussion_item == null || forcibly_set) {
            this.last_discussion_item = $('.discussion__list__item', this.getDealerContainerDiscussions()).eq(0);

            //Устаналиваем по умолчанию активную заявку
            this.params.model_id = this.last_discussion_item.data('model-id');
        }
    }

    drawUnreadDiscussionData(result) {
        $.each(result.unread_count, (index, value) => {
            $('.discussion__list__item__' + index, this.getDiscussionsContainer()).attr('data-unread', true);
            if (value > 0) {
                $('.discussion__list__item__' + index, this.getDiscussionsContainer()).attr('data-unread-count', value).append('<i>' + value + '</i>');
            }
        });
    }

    getDealerContainerDiscussions() {
        return $(this.params.container_dealer_discussions);
    }

    /**
     * Панель обычных сообщений
     * @returns {*|jQuery|HTMLElement}
     */
    getDealerContainerDiscussionsMessages() {
        return $(this.params.container_discussion_messages);
    }

    /**
     * Панель вопросов
     * @returns {*|jQuery|HTMLElement}
     */
    getDealerContainerDiscussionsAskMessages() {
        return $(this.params.container_discussion_ask_messages);
    }

    getDealersItemsList() {
        return $(this.params.on_dealer_show_discussions);
    }

    getDiscussionMessagesStatusSwitch() {
        return $(this.params.on_switch_discussions_messages_status);
    }

    getDiscussionMessagesTypesSwitch() {
        return $(this.params.on_switch_discussions_messages_type);
    }

    getDiscussionItemParent() {
        return $('#dealers-discussions-container');
    }

    /**
     * Фильтр списка дилеров по имени / номеру / пользователю
     * @param event
     */
    onFilterDiscussionsByName(event) {
        let field = $(event.target), value = $.trim(field.val()).toLowerCase();

        if (value.length > 0) {
            $('[data-user-name], [data-dealer-name], [data-dealer-number]').each(function (index, element) {
                let hide_element = true;

                if ($(element).data('user-name').toLowerCase().indexOf(value) != -1) {
                    hide_element = false;
                    $(element).show();
                }

                if ($(element).data('dealer-name').toLowerCase().indexOf(value) != -1) {
                    hide_element = false;
                    $(element).show();
                }

                if ($(element).data('dealer-number').toString().indexOf(value) != -1) {
                    hide_element = false;
                    $(element).show();
                }

                if (hide_element) {
                    $(element).hide();
                }
            });
        } else {
            this.getDiscussionListItem().show();
        }
    }

    switchPanelsByType() {
        $('.discussion-messages-list-by-type').hide();
        $('[data-container-type=' + this.params.messages_type + ']').show();
    }

    /**
     *
     * @param Event event
     */
    onMakeDealersSort(event) {
        let $element = $(event.target), dealers_list = [];

        $('.dealer_model_item').each((index, element) => {
            dealers_list.push($(element));
        });

        dealers_list.sort((a, b) => {
            let a_name = a.data('dealer-name').toLowerCase(), b_name = b.data('dealer-name').toLowerCase();

            if (a_name > b_name) {
                return $element.data('sort-direction') == 'asc' ? 1 : -1;
            }

            if (a_name < b_name) {
                return $element.data('sort-direction') == 'asc' ? -1 : 1;
            }

            return 0;
        });

        this.getDiscussionsContainer().html('');
        for (let item of dealers_list) {
            this.getDiscussionsContainer().append(item);
        }

        $element.data('sort-direction', $element.data('sort-direction') == 'asc' ? 'desc' : 'asc');
    }

    /**
     *
     * @returns {*|jQuery|HTMLElement}
     */
    getDiscussionAskMessagesContainer() {
        return $(this.params.container_discussion_ask_messages);
    }

    /**
     * Получить данные о том кому отправлять сообщение (дилеру или импортерам)
     * @returns {{direction: string}}
     */
    getMessageDirection(message_type) {
        return {
            'direction': this.getMessageSendTo(message_type) != undefined && this.getMessageSendTo(message_type).is(':checked') ? 'importer' : ''
        };
    }

    /**
     * Делаем запрос по ws для информировании пользователя о новом сообщении
     * @param data
     */
    makeRequestData(data) {
        this.makeRequest('newMessage', data);
    }

    /**
     * Получить направление отправкии сообщения (для дилера или импортера)
     * @param message_type
     * @returns {*|jQuery|HTMLElement}
     */
    getMessageSendTo(message_type) {
        if (message_type == undefined) {
            return $('.message-to-importer', $('[data-container-type="all"]'));
        }

        return $('.message-to-importer', message_type == 'message' ? $('[data-container-type="all"]') : $('[data-container-type="ask"]'));
    }

    /**
     * Обнуление панелей отображение заявок по статусу (Все или Непрочитанные)
     */
    resetDiscussionMessagesStatus() {
        $('.discussions-messages-status').removeClass('current');
        $('.discussions-messages-status').eq(0).addClass('current');
    }
}
