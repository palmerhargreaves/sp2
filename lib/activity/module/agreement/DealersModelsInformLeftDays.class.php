<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 20.10.2016
 * Time: 11:29
 */
class DealersModelsInformLeftDays
{
    const MODEL_10_DAYS_LEFT = 11;
    const MODEL_2_DAYS_LEFT = 3;
    const MODEL_BLOCKED = 'blocked';

    const MODEL_10_DAYS_LEFT_LABEL = 'left_10';
    const MODEL_2_DAYS_LEFT_LABEL = 'left_2';

    private $_messages_list = array();
    private $_entries = array();

    public function __construct()
    {
        $this->_loadModelstoSendMessages();
    }

    private function _loadModelsToSendMessages()
    {

        $models_list = AgreementModelTable::getInstance()->createQuery('am')
            ->select('am.id as am_id, am.status as am_status, amr.status as amr_status, am.is_blocked, am.allow_use_blocked, am.use_blocked_to, am.model_category_id, am.period, values.*')
            ->leftJoin('am.Report amr')
            ->innerJoin('am.Values values')
            ->where('(year(created_at) = ? or year(created_at) = ?)', array(date('Y'), date('Y') - 1))
            ->andWhere('am.model_type != ?', AgreementModel::CONCEPT_TYPE_ID)
            ->andWhere('am.status = ?', array('accepted'))
            ->andWhere('am.is_deleted = ?', false)
            ///->andWhere('am.is_blocked = ?', false)
            //->andWhere('am.status != ? or amr.status != ?', array('accepted', 'accepted'))
            ->orderBy('am.id DESC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $today = strtotime(date('d-m-Y 00:00:00'));
        //$today = strtotime(date('d-m-Y 00:00:00', strtotime('+1 days', time())));

        //$today = mktime(0,0,0,11,22,2016);
        foreach ($models_list as $model) {
            //if ($model['am_status'] == 'accepted' && ((!is_null($model['Report']) && ($model['amr_status'] == 'accepted' || $model['amr_status'] == 'wait' || $model['amr_status'] == 'wait_specialist')) || is_null($model['Report']))) {
            if ($model['am_status'] == 'accepted' && ((!is_null($model['Report']) && ($model['amr_status'] == 'accepted' || $model['amr_status'] == 'wait' || $model['amr_status'] == 'wait_specialist')))) {
                continue;
            }

            //Если заявка заблокирована
            if ($model['is_blocked'] && !$model['allow_use_blocked']) {
                continue;
            }

            /**Определяем количество дней до блокировки заявки */
            $model_period_end_value = date('d-m-Y', strtotime('+ 1 days', strtotime(AgreementModelValueTable::getPeriodValueFromModel($model, true))));
            $model_period_end = strtotime(D::getNewDate($model_period_end_value, self::MODEL_10_DAYS_LEFT, '+', false, 'd-m-Y'));

            /**
             * Если для заявки не загружен отчет
             */
            if (is_null($model['Report'])) {
                /**
                 * Если заявка разблокирована, проверяем дату окончания разблокировки, в случае если период окончен, блокируем заявку
                 */
                if ($model['is_blocked'] && !empty($model['use_blocked_to'])) {
                    $model_period_end = strtotime($model['use_blocked_to']);
                    $elapsed_days = Utils::getElapsedTime($model_period_end - $today);
                } else {
                    $model_period_end = strtotime(D::getNewDate($model_period_end_value, self::MODEL_10_DAYS_LEFT, '+', false, 'd-m-Y H:i:s', 0));
                    $elapsed_days = Utils::getElapsedTime($model_period_end - $today);
                }

                //Делаем проверку на выходные дни
                $not_work_days = 0;
                for ($day_index = 0; $day_index <= $elapsed_days; $day_index++) {
                    $tempDate = date('Y-m-d H:i:s', strtotime('-' . $day_index . ' days', D::toUnix($model_period_end)));

                    $d = getdate(strtotime($tempDate));
                    $dPlus = AgreementModel::checkDateInCalendar($tempDate);
                    if ($dPlus == 0) {
                        if ($d['wday'] == 0 || $d['wday'] == 6)
                            $not_work_days++;
                    } else if ($dPlus > 1) {
                        $day_index += $dPlus;
                    }
                }

                //Вычитаем полученные рабочие дни
                $elapsed_days -= $not_work_days;

                if ($elapsed_days <= 10 && $elapsed_days > 2) {
                    $this->addDealerNotificationAndItem($model, AgreementModelsBlockInform::INFORM_STATUS_LEFT_10, true, $elapsed_days, $model_period_end);
                    echo('add_10_report_left' . $model['am_id']);
                } else if ($elapsed_days <= 2 && $elapsed_days > 0) {
                    $this->addDealerNotificationAndItem($model, AgreementModelsBlockInform::INFORM_STATUS_LEFT_2, true, $elapsed_days, $model_period_end);
                    echo('add_2_report_left' . $model['am_id']);
                } /**
                 * Если есть отчет и до выполнения осталось 0 дней блокируем заявку
                 */
                else if ($elapsed_days <= 0) {
                    $this->addDealerNotificationAndItem($model, AgreementModelsBlockInform::INFORM_STATUS_BLOCKED, true, 0, $model_period_end);

                    echo('add_blocked_report' . $model['am_id']);
                }
            } else {
                /**
                 * Делаем проверку после истечения срока размещения, если осталось 0 дней блокируем заявку и отправляем письмо
                 * Если завка была разблокирована учитываем дату разблокировки
                 */
                if ($model['is_blocked'] && !empty($model['use_blocked_to'])) {
                    $model_period_end = $model['use_blocked_to'];
                } else {
                    //Если отчет отклонен и если дата размещения заявки меньше тек. даты, получаем данные о дате отклонения заявки
                    if ($model['amr_status'] == 'declined' && $today >= $model_period_end) {
                        $log_entry = LogEntryTable::getInstance()->createQuery()->select('created_at')->where('object_id = ? and action = ? and object_type = ?', array( $model[ 'am_id' ], 'declined', 'agreement_report' ))->orderBy('id DESC')->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                        if ($log_entry) {
                            $value = date('d-m-Y H:i:s', strtotime($log_entry[ 'created_at' ]));
                            $model_period_end = strtotime(D::getNewDate($value, 2, '+', false, 'd-m-Y H:i:s'));
                        }
                    } /*else {
                        //Если текущая дата больше чем перриод размещения, получаем дату с момента отправки отчета на согласование
                        if ($today > $model_period_end) {
                            $log_entry = LogEntryTable::getInstance()->createQuery()->select('created_at')->where('object_id = ? and action = ? and object_type = ?', array( $model[ 'am_id' ], 'edit', 'agreement_report' ))->orderBy('id DESC')->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                            if ($log_entry) {
                                $value = date('d-m-Y H:i:s', strtotime($log_entry[ 'created_at' ]));
                                $model_period_end = strtotime(D::getNewDate($value, 2, '+', false, 'd-m-Y H:i:s'));
                            }
                        }
                    }*/
                }
                $elapsed_days = Utils::getElapsedTime($model_period_end - $today);

                //Делаем проверку на выходные дни
                $not_work_days = 0;
                for ($day_index = 0; $day_index <= $elapsed_days; $day_index++) {
                    $tempDate = date('Y-m-d H:i:s', strtotime('-' . $day_index . ' days', D::toUnix($model_period_end)));

                    $d = getdate(strtotime($tempDate));
                    $dPlus = AgreementModel::checkDateInCalendar($tempDate);
                    if ($dPlus == 0) {
                        if ($d['wday'] == 0 || $d['wday'] == 6)
                            $not_work_days++;
                    } else if ($dPlus > 1) {
                        $day_index += $dPlus;
                    }
                }

                $elapsed_days -= $not_work_days;

                /**
                 * Если есть отчет и осталось до выполнения 10 дней, информируем дилера о 10 днях
                 */
                if ($elapsed_days <= 10 && $elapsed_days > 2) {
                    $this->addDealerNotificationAndItem($model, AgreementModelsBlockInform::INFORM_STATUS_LEFT_10, true, $elapsed_days, $model_period_end);
                    echo('add_10_report_left' . $model['am_id']);
                } /**
                 * Если есть отчет и осталось до выполнения 2 дня, информируем дилера о двух днях
                 */
                else if (!is_null($model['Report']) && $model['Report']['amr_status'] == 'declined' && $elapsed_days <= 2 && $elapsed_days > 0) {
                    $this->addDealerNotificationAndItem($model, AgreementModelsBlockInform::INFORM_STATUS_LEFT_2, true, $elapsed_days, $model_period_end);

                    echo('add_2_report' . $model['am_id']);
                } /**
                 * Если есть отчет и до выполнения осталось 0 дней блокируем заявку
                 */
                else if (!is_null($model['Report']) && $model['Report']['amr_status'] == 'declined' && $elapsed_days <= 0) {
                    $this->addDealerNotificationAndItem($model, AgreementModelsBlockInform::INFORM_STATUS_BLOCKED, true, 0, $model_period_end);
                    echo('add_blocked_report' . $model['am_id']);
                }
            }
        }
    }

    /**
     * @param $model
     * @param $type
     * @param bool $send_email
     * @param int $left_days
     * @param string $period_end
     */
    private function addDealerNotificationAndItem($model, $type, $send_email = true, $left_days = 0, $period_end = '')
    {
        $allow_to_inform = false;

        /*if ($type == AgreementModelsBlockInform::INFORM_STATUS_BLOCKED) {
            $allow_to_inform = true;
        } else*/
        if (AgreementModelsBlockInformTable::getInstance()->createQuery()->where('model_id = ? and block_type = ?', array($model['am_id'], $type))->count() == 0) {
            $allow_to_inform = true;
        }

        if ($allow_to_inform) {
            if ($send_email) {
                $this->addDealerNotification(AgreementModelTable::getInstance()->find($model['am_id']), $type);
            }

            $this->addBlockItem($model['am_id'], $type, $left_days, $period_end);
        }
    }

    /**
     * @param $model_id
     * @param $block_type
     * @param $left_days
     * @param $period_end
     */
    private function addBlockItem($model_id, $block_type, $left_days, $period_end)
    {
        $block_item = new AgreementModelsBlockInform();
        $block_item->setArray(
            array
            (
                'model_id' => $model_id,
                'block_type' => $block_type,
                'left_days' => $left_days,
                'period_end' => date('Y-m-d H:i:s', $period_end)
            )
        );
        $block_item->save();
    }

    public function sendMessages()
    {
        foreach ($this->_entries as $entry) {
            sfContext::getInstance()->getMailer()->send($entry);
        }
    }

    protected function addDealerNotification(AgreementModel $model, $type)
    {
        $dealer_users = UserTable::getInstance()
            ->createQuery('u')
            ->innerJoin('u.DealerUsers du WITH dealer_id=?', $model->getDealerId())
            ->where('active=?', true)
            //->groupBy('du.dealer_id')
            ->execute();

        foreach ($dealer_users as $user) {
            if ($user->getAllowReceiveMails()) {
                $this->addNotificationForUser($model, $user, $type);
            }
        }

        $user = UserTable::getInstance()->find(1);
        if ($user) {
            $this->addNotificationForUser($model, $user, $type);
        }

        $user = UserTable::getInstance()->find(946);
        if ($user) {
            $this->addNotificationForUser($model, $user, $type);
        }
    }

    protected function addNotificationForUser(AgreementModel $model, User $user, $type)
    {
        $message = new DealersModelsBlockInformLeftDays($user, $model, $type);
        $message->setPriority(1);

        $this->_entries[] = $message;

        /**
         * Утанавливаем параметры для заявки
         * Тип отправляемых писем
         * И если заявка блокируется устанавливаем соответсвующие параметры
         */
        $model->setBlockedInformStatus($type);
        if ($type == AgreementModelsBlockInform::INFORM_STATUS_BLOCKED) {
            $model->setIsBlocked(true);
            $model->setBlockedInform(2);
            $model->setAllowUseBlocked(false);
            $model->setUseBlockedTo('');
        }
        $model->save();

        //echo  $user->getEmail()." - ";
    }

}
