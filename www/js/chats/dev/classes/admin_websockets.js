/**
 * Created by kostet on 02.09.2017.
 */

import DiscussionWebSockets from './websockets';

export default class AdminWebSockets extends DiscussionWebSockets {
    constructor() {
        super();
    }

    /*onMessage(event) {
        let data = JSON.parse(event.data);

        if (data.action == 'newMessage') {
            console.log(data.data.users_who_get_messages);
        }
    }*/

}
