/**
 * Created by kostet on 09.08.2017.
 */

import DiscussionWebSockets from './websockets';

export default class DealerWebSockets extends DiscussionWebSockets {
    constructor() {
        super();
    }

    onMessage(event) {
        console.log(event);
    }
}
