/**
 * Created by kostet on 04.09.2017.
 */

import DiscussionWebSockets from "./websockets";

export default class ChatByWS extends DiscussionWebSockets {
    constructor() {
        super();

        this.initialized = false;
        this.in_chat = false;
        this.is_open = true;
        this.new_messages_counter = 0;
        this.discussion_model_messages_chat_uploader = undefined;
        this.message_type = null;

        this.discussion_id = 0;
        this.discussions_list = {};
        this.model_id = 0;

        this.clean_chat_items_in_minutes = 5;
    }

    init() {
        if (!this.initialized) {
            this.initialized = true;

            $(document).on('click', '#discussion-live-chat header', () => {
                $('.discussion-chat, .discussion-chat-users').slideToggle(300, 'swing');
                $('.chat-message-counter').fadeToggle(300, 'swing');

                if (!this.is_open) {
                    this.getChatMessageCounterElement().html(0);
                    this.new_messages_counter = 0;
                }

                //Спускаемся до последнего сообщения
                this.scrollDown('chat-history');

                this.is_open = !this.is_open;
            });

            $(document).on('click', '.chat-close', (e) => {
                e.preventDefault();
                $('#discussion-live-chat').fadeOut(300);

                this.in_chat = false;
            });

            //Переключение чатов
            $(document).on('click', '.discussion-chat-user-item', $.proxy(this.onSwitchChat, this));

            //Удаление чата (двойной клик)
            $(document).on('dblclick', '.discussion-chat-user-item', $.proxy(this.onDestroyChat, this));

            $(document).on('click', '#frm-discussion-chat-send-button', $.proxy(this.onSendChatMessage, this));

            $(document).on('keyup', '#frm-message-chat', $.proxy(this.onMessagesFieldEnterKeyPress, this));

            //Если дискуссия уже велась отображаем диалог дискусси между пользователями, берем данные с localStorage
            this.loadChatDiscussionsIfWeHaveAlreadyChatsWithUsers();
            //$document

            //Очистка данных в чате если время последней беседы больше 5 минут
            this.cleanChats();
        }
    }

    onOpen() {
        super.onOpen();

        this.init();
    }

    /**
     * Отправка сообщение по Enter
     * @param event
     */
    onMessagesFieldEnterKeyPress(event) {
        if (event.keyCode == 13) {
            this.onSendChatMessage(event);
        }
    }

    //Отправить сообщение
    onSendChatMessage(event, callback) {
        event.preventDefault();

        let element = $(event.target), send_url = element.closest('form').data('send-url'), total_uploaded_files = 0, active_discussion_item = this.getActiveUserChat();

        if (active_discussion_item.length == 0) {
            showAlertPopup('Ошибка.', 'Выберите чат.');
            return;
        }

        let discussion_item = this.discussions_list[active_discussion_item.data('discussion-id')];

        this.discussion_id = discussion_item.discussion_id;
        this.discussions_list[this.discussion_id].time = Date.now();

        for (var i in this.getFilesParam(this.discussion_model_messages_chat_uploader)) {
            if (this.getFilesParam(this.discussion_model_messages_chat_uploader).hasOwnProperty(i)) {
                total_uploaded_files++;
            }
        }

        if (this.posting || ($.trim(this.getMessage()) == '' && total_uploaded_files == 0)) {
            showAlertPopup('Ошибка.', 'Введите сообщение или загрузите файл.');
            return;
        }

        let data = $.extend(this.getFilesParam(), {
                id: discussion_item.discussion_id,
                model_id: discussion_item.model_id,
                dealer_id: discussion_item.dealer_id,
                message: this.getMessage(),
                reply_on_message_id: 0,
                message_type: discussion_item.message_type,
            }
        );

        this.posting = true;
        new Promise((resolve, reject) => {
            $.post(send_url,
                data,
                (result) => {
                    resolve(result);
                    this.posting = false;
                })
        }).then((result) => {
            this.getMessageField().val('');
            this.deleteSentFiles(this.discussion_model_messages_chat_uploader);

            if (result.response != undefined) {
                this.getChatDialogContentContainer().html(result.response.messages_list);

                this.scrollDown('chat-history');

                data = $.extend(result.response, data);

                this.sendMessageToOthersUsers(data);
                this.saveDiscussionList();
            }

            if (callback != undefined) {
                callback(result);
            }
        });
    }

    /**
     * Удаление чата
     * @param event
     */
    onDestroyChat(event) {
        let element = $(event.target), discussion_id = element.data('discussion-id'), discussions_list = {};

        element.remove();
        $.each(this.discussions_list, (index, item) => {
            if (item.discussion_id != discussion_id) {
                discussions_list[item.discussion_id] = item;
            }
        });

        this.getUserNameElement().html('');

        this.discussions_list = discussions_list;

        //Очищаем список сообщений
        this.getChatDialogContentContainer().html('');

        //Созраняем список дискуссий в локальную память
        this.saveDiscussionList();

        //Если количество дискуссий 0, скрываем диалог
        if (this.getDiscussionsCountInChat() <= 0) {
            //Убараем список сообщений
            this.getChatDialogContentContainer().html('');
            this.getChatMessageCounterElement().html('0');

            $('#discussion-live-chat header').trigger('click');
        } else {
            this.getDiscussionChatItem().eq(0).trigger('click');
        }
    }

    /**
     * Загрузка последних чатов с пользователями
     */
    loadChatDiscussionsIfWeHaveAlreadyChatsWithUsers() {
        //if (window.localStorage && window.localStorage.getItem('discussions_list') != undefined) {
            let open_dialog = setInterval(() => {
                if (this.getChatDialog().length > 0) {
                    //this.discussions_list = JSON.parse(window.localStorage.getItem('discussions_list'));
                    if (amplify.store.localStorage('discussions_list') != undefined) {
                        this.discussions_list = JSON.parse(amplify.store.localStorage('discussions_list'));
                        $.each(this.discussions_list, (index, item) => {
                            this.getChatDiscussionsUsersListContainer().prepend(this.formatChatUserItem(item, item.selected));

                            if (item.selected) {
                                this.discussion_id = item.discussion_id;

                                //Загрузка списка сообщений для последней активной дискуссии
                                this.loadDiscussionMessages($(`[data-discussion-id="${this.discussion_id}"]`));
                            }
                        });

                        if (this.getDiscussionsCountInChat() == 0) {
                            $('#discussion-live-chat').fadeOut(300);
                        } else {
                            this.openChatDialog(true);
                        }

                        clearInterval(open_dialog);
                    }
                }
            }, 1000);
        //}
    }

    /**
     * Переключение чата пользователей
     * @param event
     */
    onSwitchChat(event) {
        let element = $(event.target);

        if (element.data('discussion-id') == undefined) {
            element = element.parent();
        }

        this.discussion_id = element.data('discussion-id');

        this.discussions_list[this.discussion_id].new_messages_counter = 0;
        this.setDiscussionChatItemCounter(0);

        //Обнуляем список выбора
        $.each(this.discussions_list, (index, item) => {
            this.discussions_list[index].selected = false;
        });

        this.discussions_list[this.discussion_id].selected = true;

        this.getDiscussionChatItem().removeClass('active-chat-user-item');
        element.addClass('active-chat-user-item');

        this.getUserNameElement().html(this.discussions_list[this.discussion_id].chat_with_user);

        //Делаем пересохранение списка открытых чатов с учетом последнего выбранного
        this.saveDiscussionList();

        //Загрузка списка сообщение для выбранной дискуссии
        this.loadDiscussionMessages(element)
    }

    /**
     * Загрузка списка сообщение для выбранной дискуссии
     * @param element
     */
    loadDiscussionMessages(element) {
        $.post(element.closest('#discussion-live-chat').data('load-chat-url'), {
            model_id: this.discussions_list[this.discussion_id].model_id,
            messages_type: this.discussions_list[this.discussion_id].message_type,
        }, $.proxy(this.onLoadChatMessagesResult, this));
    }

    /**
     * Добавление сообщений
     * @param data
     */
    onLoadChatMessagesResult(data) {
        this.getChatDialogContentContainer().html('Нет сообщений.');
        if (data.success) {
            this.getChatDialogContentContainer().html(data.messages_list);

            this.scrollDown('chat-history');
        }
    }


    /**
     * Получение сообщения и разбор его
     * @param event
     */
    onMessage(event) {
        let data = JSON.parse(event.data), allow_append = true;

        console.log(data);
        if (data.action == 'newMessage') {
            if (data.data.users_who_get_messages != undefined && data.data.users_who_get_messages.length > 0) {
                for (let item of data.data.users_who_get_messages) {
                    if ($('[data-auth-user-id=' + item + ']').length > 0) {
                        this.getUserNameElement().html(data.data.users_names);

                        //Номера дискуссии и заявки
                        this.discussion_id = data.data.discussion_id;

                        //Создаем список активных дискуссий с кем переписывается пользователь
                        if (this.discussions_list[this.discussion_id] == undefined) {
                            this.discussions_list[this.discussion_id] = {
                                model_id: data.data.model_id,
                                dealer_id: data.data.dealer_id,
                                discussion_id: data.data.discussion_id,
                                message_type: data.data.message_type,
                                new_messages_counter: parseInt(data.data.messages_count),
                                chat_with_user: data.data.users_names,
                                model_url: data.data.model_url,
                                time: Date.now(),
                                selected: false
                            }
                        } else {
                            this.discussions_list[this.discussion_id].new_messages_counter += parseInt(data.data.messages_count);
                            this.discussions_list[this.discussion_id].time = Date.now();

                            this.setDiscussionChatItemCounter(this.discussions_list[this.discussion_id].new_messages_counter);

                            //Если отправляется сообщение в активный чат, обновляем список сообщений
                            let active_chat = this.getActiveUserChat();
                            if (active_chat.length > 0 && active_chat.data('discussion-id') == this.discussion_id) {
                                this.getChatDialogContentContainer().html(data.data.messages_list);
                            }

                            allow_append = false;
                        }

                        //По умолчанию добавляем только первое сообщение от пользователя, в дальнейшем пользователь сам переключает чаты

                        if (this.getDiscussionsCountInChat() == 1) {
                            if (data.data.messages_list.length > 0) {
                                this.getChatDialogContentContainer().html(data.data.messages_list);
                            }

                            //Отметка о последней выбранной дискуссии
                            this.discussions_list[this.discussion_id].selected = true;
                        }

                        let discussion_item = this.discussions_list[this.discussion_id];
                        if (allow_append) {
                            this.getChatDiscussionsUsersListContainer().prepend(
                                this.formatChatUserItem(discussion_item, this.discussion_id == discussion_item.discussion_id && this.getDiscussionsCountInChat() == 1, data.data.messages_list.length == 0)
                            );

                            //Плавный вывод последнего добавленного сообщения
                            this.getDiscussionChatItem().eq(0).fadeIn();
                        }

                        //Принудительная загрузка списка сообщений
                        if (this.discussions_list[this.discussion_id].selected) {
                            $(`[data-discussion-id="${data.data.discussion_id}"]`).trigger('click');
                        }

                        //this.discussions_list[discussion_item.discussion_id].new_messages_counter = 0;

                        //Учитываем общее количество новых сообщений
                        this.new_messages_counter += parseInt(data.data.messages_count);
                        this.getChatMessageCounterElement().html(this.new_messages_counter);

                        if (!this.isChatOpen()) {
                            this.openChatDialog();
                        }

                        this.saveDiscussionList();
                        //this.messages.showInfo(data.data.message, 5000);
                    }
                }
            }
        }
    }

    /**
     * Установить количество новых сообщений
     */
    setDiscussionChatItemCounter(counter) {
        addShakeAnim(`[data-discussion-id="${this.discussion_id}"]`, null);

        $(`.chat-message-user-counter-${this.discussion_id}`).html(counter);
    }

    openChatDialog(collapsed = false) {
        if (!this.in_chat) {
            this.in_chat = true;

            if (collapsed) {
                this.getChatDialogHeader().show();
                this.getChatMessageCounterElement().show();

                this.getDiscussionChat().hide();
                this.getChatDiscussionUsersList().hide();
            }

            this.getChatDialog().fadeIn();

            if (this.discussion_model_messages_chat_uploader == undefined) {
                this.discussion_model_messages_chat_uploader = new JQueryUploader({
                    file_uploader_el: '#discussion_message_files_chat',
                    max_file_size: '5485760',
                    uploader_url: '/upload_ajax.php',
                    delete_temp_file_url: '/temp_file_ajax/delete',
                    delete_uploaded_file_url: '/agreement/model/delete/uploaded/file',
                    uploaded_files_container: '#discussion_files_chat',
                    el_attach_files_model_field: '#discussion_message_files_chat',
                    progress_bar: '#discussion-messages-files-progress-bar-chat',
                    upload_files_ids_el: 'upload_files_discussion_ids',
                    upload_file_object_type: 'discussion',
                    upload_file_type: 'discussion',
                    upload_field: 'discussion_message_files_chat',
                    draw_only_labels: true,
                    //el_attach_files_click_bt: '#discussion_message_files',
                    disabled_files_extensions: ['js'],
                    model_form: '#frm-discussion-model-messages-chat'
                }).start();
            }
        }
    }

    isChatOpen() {
        return this.in_chat;
    }

    getChatDialog() {
        return $('#discussion-live-chat');
    }

    getChatDialogHeader() {
        return $('header', this.getChatDialog());
    }

    getChatDialogContentContainer() {
        return $('.chat-history', this.getChatDialog());
    }

    getUserNameElement() {
        return $('#discussion-chat-user-name');
    }

    getChatMessageCounterElement() {
        return $('.chat-message-counter');
    }

    getChatDiscussionUsersList() {
        return $('.discussion-chat-users');
    }

    getDiscussionChat() {
        return $('.discussion-chat');
    }

    getChatDiscussionsUsersListContainer() {
        return $('.chat-message-content', this.getChatDiscussionUsersList());
    }

    getForm() {
        return $('#frm-discussion-model-messages-chat');
    }


    /**
     * Созраняем список открытых дискуссий на случай если пользователь перейдет по ссылке, чат должен автоматически открываться
     */
    saveDiscussionList(discussions_list = undefined) {
        amplify.store.localStorage('discussions_list', JSON.stringify(discussions_list != undefined ? discussions_list : this.discussions_list));
        //if (window.localStorage) {
            //window.localStorage.setItem();
        //}
    }

    /**
     * Форматирование вывода пользователя
     * @returns {string}
     * @param discussion_item
     * @param active
     */
    formatChatUserItem(discussion_item, active, forcibly_click) {
        if (active) {
            this.getUserNameElement().html(discussion_item.chat_with_user);
        }

        return `
            <div class="discussion-chat-user-item ${active ? 'active-chat-user-item' : ''} ${forcibly_click != undefined && forcibly_click ? 'allow-click' : ''}" 
                data-discussion-id="${discussion_item.discussion_id}" 
                data-model-id="${discussion_item.model_id}"
                data-dealer-id="${discussion_item.dealer_id}"
            >
                ${discussion_item.chat_with_user}
                <span class="chat-message-user-counter chat-message-user-counter-${discussion_item.discussion_id}">${discussion_item.new_messages_counter}</span>
                <span class="chat-message-user-model"><a href="${discussion_item.model_url}">№${discussion_item.model_id}</a></span>
                <!--<a href="javascript:;" data-discussion-id="${discussion_item.discussion_id}" class="chat-user-close">x</a>-->
            </div>
        `;
    }

    /**
     * Очистка данных по чатам с истекшим времени последнего общения
     */
    cleanChats() {
        setInterval(() => {
            if (this.getDiscussionsCountInChat() > 0) {
                let current_time = Date.now(), discussions_list =  {};

                $.each(this.discussions_list, (index, item) => {
                    let diff_time = current_time - item.time;

                    diff_time /= 1000; //убираем милисекунды
                    diff_time = Math.round(diff_time / 60); //убираем секунды

                    //Если время больше чем установлено удаляем запись из чата
                    if (diff_time > this.clean_chat_items_in_minutes && !this.discussions_list[item.discussion_id].selected) {
                        //$(`[data-discussion-id="${item.discussion_id}"]`).remove();
                        $(`[data-discussion-id="${item.discussion_id}"]`).remove();

                        //Если не осталось не одного чата удаляем все данные по чату
                        if (this.getDiscussionsCountInChat() == 0) {
                            this.getChatDialogContentContainer().html('');
                            this.getUserNameElement().html('');
                        }
                    } else {
                        discussions_list[item.discussion_id] = this.discussions_list[item.discussion_id];
                    }
                });

                //Сохраняем данные в локальную память
                this.saveDiscussionList(discussions_list);
            }
        }, 5000);
    }

    getActiveUserChat() {
        return $('.active-chat-user-item');
    }

    getMessage() {
        return $.trim(this.getMessageField().val());
    }

    getMessageField() {
        return $('#frm-message-chat');
    }

    /**
     * Отправка нового сообщения пользователям
     */
    sendMessageToOthersUsers(data) {
        this.makeRequest('newMessage', data);
    }

    /**
     * Чат с пользователем
     * @returns {*|jQuery|HTMLElement}
     */
    getDiscussionChatItem() {
        return $('.discussion-chat-user-item');
    }

    /**
     * Получаем количество активных дискуссий
     * @returns {number}
     */
    getDiscussionsCountInChat() {
        let count = 0;

        $.each(this.discussions_list, (index, item) => {
            count++;
        });

        return count;
    }
}
