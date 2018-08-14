/**
 * Created by kostet on 03.08.2017.
 */

import Admin from './admin';

export default class Importer extends Admin {
    constructor(params) {
        super(params);
    }

    /**
     * Получить данные о том кому отправлять сообщение (дилеру или импортерам)
     * @returns {{direction: string}}
     */
    getMessageDirection() {
        return {
            'direction': 'admin_dealer'
        };
    }
}
