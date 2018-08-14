
import ChatByWS from './classes/chat_by_ws';

import Dealer from './classes/dealer';
import Admin from './classes/admin';
import Importer from './classes/importer';

import DealerWebSockets from './classes/dealer_websockets';
import AdminWebSockets from './classes/admin_websockets';
import ImporterWebSockets from './classes/importer_websockets';

export default class DiscussionOnlineApp {
    startDealer(params) {
        //params.websockets = new DealerWebSockets();

        return new Dealer(params).start();
    }

    startAdmin(params) {
        //params.websockets = new AdminWebSockets();

        return new Admin(params).start();
    }

    startImporter(params) {
        //params.websockets = new ImporterWebSockets();

        return new Importer(params).start();
    }
}

if (RegExp('messages', 'gi').test(window.location.href)) {
    if (window.discussion_online == undefined) {
        window.discussion_online = new DiscussionOnlineApp();
    }
} else {
    if (window.discussion_online == undefined) {
        window.discussion_online = new ChatByWS();
    }
}

