/**
 * Created by kostet on 09.08.2017.
 */

import BaseDiscussion from './base_discussion';

export default class DiscussionWebSockets extends BaseDiscussion {
    constructor(params) {
        super(params)

        this.openConnection();

        this.status = 'closed';
        this.messages = new Messages().start();
        this.messages.setPosition('toast-bottom-right');
    }

    start() {
        super.start();
    }

    onOpen(event) {
        this.status = 'open';

        console.log(this.status);
    }

    onClose(event   ) {
        this.status = 'closed';

        console.log(event);

        this.openConnection();
    }

    onMessage(event) {
        let data = JSON.parse(event.data);

        console.log(data);
        if (data.action == 'newMessage') {
            if (data.data.users_who_get_messages != undefined && data.data.users_who_get_messages.length > 0) {
                for (let item of data.data.users_who_get_messages) {
                    if ($('[data-auth-user-id=' + item + ']').length > 0) {
                        this.getDiscussionChatBySidebarPanel().prepend(this.formatSidebarChatItem(data.data));

                        //Обновляем счетчик сообщений
                        let counter = parseInt(this.getDiscussionChatBySidebarCounter().text());

                        this.getDiscussionChatBySidebarCounter().text(++counter);
                        addShakeAnim('#js-chat-messages-counter');

                        //Для дилеров подгружаем список сообщений из раздела Задать вопрос
                        this.reloadAskMessages(data.data);
                    }
                }
            }
        }
    }

    onError(event) {

    }

    onSend(data) {
        console.log(this.status);

        if (this.status == 'open') {
            this.web_sockets.send(JSON.stringify(data));
        }
    }

    makeRequest(action, data) {
        let send_data = {};

        send_data.action = action;
        send_data.data = data;

        return this.onSend(send_data);
    }

    /**
     * Информирование системы о новым сообщении
     * @param data
     * @returns {*}
     */
    onHaveNewMessage(data) {
        if (data != null) {
            return this.makeRequest('newMessage', data);
        }
    }

    openConnection() {
        console.log('test connection');

        this.web_sockets = new WebSocket('ws://dm.vw-servicepool.ru:48880/discussion');
        this.web_sockets.onopen = (event) => { this.onOpen(event) };
        this.web_sockets.onclose = (event) => { this.onClose(event) };
        this.web_sockets.onmessage = (event) => { this.onMessage(event) };
        this.web_sockets.onerror = (event) => { this.onError(event) };
    }
}
