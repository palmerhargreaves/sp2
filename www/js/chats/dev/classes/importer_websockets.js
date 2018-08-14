/**
 * Created by kostet on 02.09.2017.
 */

import AdminWebSockets from './admin_websockets';

export default class ImporterWebSockets extends AdminWebSockets {
    constructor() {
        super();
    }

    onMessage(event) {
        console.log(event);
    }
}


