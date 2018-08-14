<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 04.12.2017
 * Time: 16:25
 */

class ActivitiesNotUsingImporterMails {
    private $_entries = array();

    public function __construct()
    {
        $this->getData();
    }

    /**
     * Заполняем данными массив для рассылки
     */
    private function getData() {
        $current_date = D::calcQuarterData(time());
        $current_q = D::getQuarter($current_date);
        $current_y = D::getYear($current_date);

        $result = array();

        $activities_with_not_using_importer = ActivityVideoRecordsStatisticsTable::getInstance()->createQuery()->where('not_using_importer = ?', true)->execute();
        foreach ($activities_with_not_using_importer as $item) {
            if (!$item->getActivity()->getFinished()) {
                $models = AgreementModelTable::getInstance()->createQuery()
                    ->where('(quarter(created_at) = ? or quarter(updated_at) = ?) and year(created_at) = ? and activity_id = ?',
                        array
                        (
                            $current_q,
                            $current_q,
                            $current_y,
                            $item->getActivityId()
                        )
                    )->execute();

                foreach ($models as $model) {
                    if ($model->getDealer()->getDealerUsers()->getFirst()) {
                        $user = $model->getDealer()->getDealerUsers()->getFirst()->getUser();
                        $result[$item->getActivityId()][$model->getDealerId()] = array(
                            'user_mail' => $user->getEmail(),
                            'user_name' => $user->selectName(),
                            'dealer_id' => $model->getDealerId(),
                            'activity' => array(
                                'id' => $item->getActivityId(),
                                'name' => $item->getActivity()->getName()
                            )
                        );
                    }
                }
            }
        }

        foreach($result as $activity_id => $items) {
            foreach ($items as $dealer_id => $item_data) {
                $this->_entries[] = new ActivitiesNotUsingImporterMail($item_data);
            }
        }
    }

    /**
     * Отправка писем
     */
    public function send() {
        foreach ($this->_entries as $entry) {
            sfContext::getInstance()->getMailer()->send($entry);
        }
    }
}
