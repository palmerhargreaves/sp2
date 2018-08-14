/**
 * Created by kostet on 03.08.2017.
 */

import DiscussionWebSockets from "./websockets";

export default class Dealer extends DiscussionWebSockets {
    constructor(params) {
        super(params);

        this.msg_field = params.frm_discussion_message_element;
        this.scroll_container = 'container-model-messages';
    }

    start() {
        super.start();

        $(document).on('change', this.params.on_models_by_activity, $.proxy(this.onGetDiscussionMessagesByActivity, this));

        //Загрузка списка сообщенией при загрузке страницы
        //Для первой завке в списке
        this.loadDiscussionMessages();

        return this;
    }

        /**
     * Получение списка дискуссий по выбранной активность
     * @param event
     */
    onGetDiscussionMessagesByActivity(event) {
        let filter = $(event.target), filter_value = filter.val();

        this.getDiscussionVisibilityButtons().removeClass('current');
        this.getDiscussionVisibilityButtons().eq(0).addClass('current');

        this.filter.filter_by_activity = filter_value;
        this.loadDiscussionList();
    }

    /**
     * Загрузка списка дискуссий
     */
    loadDiscussionList() {
        new Promise((resolve, reject) => {
            Pace.start();
            $.post(this.params.on_load_discussions_url,
                this.filter,
                (result) => {
                    resolve(result);
                })
        })
            .then((result) => {
                Pace.stop();

                if (result.first_message.model != undefined) {
                    this.params.default_model_id = result.first_message.model.id;
                    this.params.model_id = undefined;
                }
                this.getDiscussionsContainer().html(result.discussions);

                //Подгружаем список сообщений для первой дискуссии из выбранной активности в списке
                this.loadDiscussionMessages();
            })
            .catch((result) => {
                Pace.stop();
            });
    }

    getDiscussionsContainer() {
        return $('#dealer-discussions-container');
    }

    /**
     * Делаем запрос по ws для информировании пользователя о новом сообщении
     * @param data
     */
    makeRequestData(data) {
        this.makeRequest('newMessage', data);
    }

    reloadAskMessages(data) {
        if (data.message_type == 'ask') {
            this.loadAskMessages();
        }
    }
}
