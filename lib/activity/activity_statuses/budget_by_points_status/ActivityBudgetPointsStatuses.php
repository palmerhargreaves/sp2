<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 20.03.2018
 * Time: 13:02
 */

class ActivityBudgetPointsStatuses extends ActivityStatusBase {

    public function getStatus()
    {
        //Получаем данные по заявкам и статистики
        parent::getStatus();

        //Получаем данные по заполненной статистки
        $this->fields_values = ActivityExtendedStatisticFieldsDataTable::getInstance()
            ->createQuery()
            ->where('dealer_id = ?', $this->dealer->getId())
            ->andWhere('activity_id = ?', $this->activity->getId())
            ->andWhere('quarter = ?', $this->quarter)
            ->andWhere('year = ?', $this->year)
            ->count();

        //Если нет заполненных данных, проверяем активность на наличие привязанной статистики
        if ($this->fields_values == 0) {
            $this->fields_values = ActivityExtendedStatisticFieldsTable::getInstance()
                ->createQuery()
                ->andWhere('activity_id = ?', $this->activity->getId())
                ->count();
        }

        //Принудительное выполнение активности
        if (ActivitiesStatusByUsersTable::checkActivityStatus($this->activity->getId(), $this->dealer->getId(), $this->year, $this->quarter)) {
            return array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_TOTAL_COMPLETED, 'msg' => 'Активность выполнена' );
        }

        //Спец. согласование по активности с учетом выполнения концепции
        if ($this->activity->getAllowSpecialAgreement()) {
            $concept = AgreementModelTable::getInstance()->createQuery('m')
                ->innerJoin('m.Report r')
                ->where('m.dealer_id = ? and m.activity_id = ? and m.model_type_id = ?', array( $this->dealer->getId(), $this->activity->getId(), Activity::CONCEPT_MODEL_TYPE_ID ))
                ->andWhere('m.status = ? and r.status = ?', array('accepted', 'accepted'))
                ->fetchOne();

            if (!$concept) {
                return $this->activity_models_created_count > 0
                    ? array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_IN_WORK, 'msg' => 'К активности приступили' )
                    : array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_NOT_START, 'msg' => 'Активность не начата' );
            }
        }

        //Проверка на выполнение для активностей с статистикой
        if ($this->activity_models_created_count > 0) {
            $activity_completed = Utils::checkModelsCompleted($this->activity, $this->dealer, $this->year, $this->quarter);

            //Проверка на выполнение концепции
            if (!$this->activity->isConceptComplete($this->dealer, $this->year, $this->quarter)) {
                $activity_completed = false;
            }

            //Если статистика заполнена
            if ($this->fields_values > 0) {
                $activity_statistic_completed = $this->activity->isActivityStatisticComplete($this->dealer, null, true, $this->year, $this->quarter, $this->consider_activity_quarter ? array('check_by_quarter' => true) : null);
                
                //Полное выполнение активности и статистики
                if ($activity_completed && $activity_statistic_completed) {
                    return array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_TOTAL_COMPLETED, 'msg' => 'Активность выполнена, статистика заполнена' );
                }

                //Выполнены только заявки, статистика не заполнена
                if ($activity_completed && !$activity_statistic_completed) {
                    return array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_COMPLETED_WITHOUT_STATISTIC, 'msg' => 'Активность выполнена, но статистика не заполнена' );
                }
            } else if ($activity_completed) {
                return array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_TOTAL_COMPLETED, 'msg' => 'Активность выполнена' );
            }

            //В активности есть созданные заявки
            return array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_IN_WORK, 'msg' => 'К активности приступили' );
        }

        //Если в активности ничего не создано
        return array( 'status' => ActivitiesBudgetByControlPoints::ACTIVITY_NOT_START, 'msg' => 'Активность не начата' );
    }
}
