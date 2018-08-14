<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 24.11.2017
 * Time: 9:35
 */

class ActivityMailsByStatisticSteps
{
    private $_send_data = array();

    public function __construct ()
    {
        $this->makeSendMailsData();
    }

    public function start ()
    {

    }

    private function makeSendMailsData ()
    {
        $activities = ActivityTable::getInstance()->createQuery()->where('allow_extended_statistic = ? and finished = ?', array( true, false ))->orderBy('position ASC')->execute();

        $today = time();

        //Проходим по активным дилерам
        foreach (DealerTable::getInstance()->createQuery()->where('status = ?', true)->where('id = ?', 483)->execute() as $dealer) {
            if (!$dealer->getDealerUsers()->getFirst()) {
                continue;
            }

            $user = $dealer->getDealerUsers()->getFirst()->getUser();

            //Проходим по активностям с типом ServiceClinic
            foreach ($activities as $activity) {
                $mails_to_send = array();

                $concepts = AgreementModelTable::getInstance()->createQuery()->where('activity_id = ? and dealer_id = ? and model_type_id = ?', array( $activity->getId(), $dealer->getId(), AgreementModel::CONCEPT_TYPE_ID ))->orderBy('id DESC')->execute();

                //Проходим по списку полученных концепций для получение списка кварталов по активности и дилеру
                foreach ($concepts as $concept) {
                    $quartersModels = new ActivityQuartersModelsAndStatistics($user, $activity);
                    $quarters_list = $quartersModels->getData();

                    $quarter_and_year = array();
                    foreach ($quarters_list as $year_key => $quarter_data) {
                        foreach ($quarter_data as $q_key => $q_item) {
                            $quarter_and_year[ $year_key ][] = $q_key;
                        }
                    }

                    //Проверка на завршение мероприятия
                    $concept_date_item = AgreementModelDatesTable::getInstance()->createQuery()->where('model_id = ?', $concept->getId())->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                    $concept_settings = AgreementModelSettingsTable::getInstance()->createQuery()->where('model_id = ?', $concept->getId())->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                    //Заполняем данными для расслки писем
                    foreach ($quarter_and_year as $year_key => $q_keys) {
                        foreach ($q_keys as $q_key) {
                            if ($concept_date_item) {
                                $concept_date = explode("/", $concept_date_item[ 'date_of' ]);
                                $concept_date = strtotime(date("Y-m-d", strtotime(array_pop($concept_date) . ' +3 days')));

                                if ($concept_date < $today) {
                                    //$mails_to_send[ $year_key ][ $q_key ][$concept->getId()]['mail_action_end'] = array('concept' => $concept->getId());
                                    $mails_to_send[ $year_key ][$concept->getId()]['mail_action_end'] = array('concept' => $concept->getId(), 'q' => $q_key);
                                }
                            }

                            //Проверка на завершение сертификата
                            if ($concept_settings) {
                                $concept_date = strtotime(date("Y-m-d", strtotime($concept_settings[ 'certificate_date_to' ] . ' +10 days')));

                                if ($concept_date < $today) {
                                    //$mails_to_send[ $year_key ][ $q_key ][$concept->getId()]['mail_certificate_end'] = array('concept' => $concept->getId());
                                    $mails_to_send[ $year_key ] [$concept->getId()]['mail_certificate_end'] = array('concept' => $concept->getId(), 'q' => $q_key);
                                }
                            }
                        }
                    }
                }

                //Дополнительная проверка на наличие данных для рассылки
                if (!empty($mails_to_send)) {
                    //Список для рассылки писем по типам
                    foreach ($mails_to_send as $y_key => $mail_items) {
                        foreach ($mail_items as $q_key => $q_item_datas) {
                            foreach ($q_item_datas as $q_action => $q_data) {

                                //Если для текущих данных статистика уже заполнена письма не отправляем
                                $is_completed_statistic = ActivityDealerStaticticStatusTable::getInstance()->createQuery()
                                    ->where('dealer_id = ? and activity_id = ? and q'.$q_data['q'].' = ? and concept_id = ? and ignore_q'.$q_data['q'].'_statistic = ?',
                                        array(
                                            $dealer->getId(),
                                            $activity->getId(),
                                            $q_data['q'],
                                            $q_data['concept'],
                                            0
                                        ))->count() > 0;

                                //var_Dump($q_data['concept']);
                                if (!$is_completed_statistic) {
                                    //Проходим по привязанным шагам к активности
                                    $step_by_action = ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ? and step_type = ?', array( $activity->getId(), $q_action ))->fetchOne();

                                    if ($step_by_action) {
                                        $step_status = ActivityExtendedStatisticStepStatusTable::getInstance()->createQuery()
                                            ->where('step_id = ? and activity_id = ? and dealer_id = ? and concept_id = ? and year = ? and quarter = ?',
                                                array
                                                (
                                                    $step_by_action->getId(),
                                                    $activity->getId(),
                                                    $dealer->getId(),
                                                    $q_data[ 'concept' ],
                                                    $y_key,
                                                    $q_data[ 'q' ]
                                                ))->fetchOne();

                                        if ($step_status && $step_status->getStatus()) {
                                            continue;
                                        }

                                        $mail = new ActivityStatisticStepsSendMail($user, $activity, $q_action);
                                        $mail->setPriority(1);

                                        sfContext::getInstance()->getMailer()->send($mail);
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }

        exit;
    }

    /**
     * Получить список кварталов по созданным заявкам в
     * @param $activity_id
     * @return array
     */
    private function getQuarters ( $activity_id )
    {
        $query = AgreementModelTable::getInstance()
            ->createQuery()
            ->where('activity_id = ?', array( $activity_id ));

        $items = $query->execute();

        $qs = array();
        /** @var AgreementModel $item */
        foreach ($items as $item) {
            $date = $item->getModelQuarterDate();

            $q = D::getQuarter($date);
            $year = D::getYear($date);

            $qs[ $year ][ $q ] = array( 'quarter' => $q, 'year' => $year );
        }

        return $qs;
    }
}