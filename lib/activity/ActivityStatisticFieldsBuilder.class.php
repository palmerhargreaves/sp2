<?php

/**
 * Description of ActivityStatisticFieldsBuilder
 *
 *
 */
class ActivityStatisticFieldsBuilder
{
    protected $year;
    protected $quarter = null;
    protected $stat = array();

    protected $activity = null;
    protected $user = null;

    protected $dealers = null;

    function __construct ( $dataFilter, $activity, $user )
    {
        $this->year = $dataFilter[ 'year' ];

        if (isset($dataFilter[ 'quarter' ]) && ( $dataFilter[ 'quarter' ] != -1 && $dataFilter[ 'quarter' ] != 0 )) {
            $this->quarter = $dataFilter[ 'quarter' ];
        }

        $this->activity = $activity;
        $this->user = $user;

        $this->loadDealers();

        $this->build();
        $this->buildByActivity();
        $this->buildFields();
    }

    function loadDealers ()
    {
        if ($this->dealers !== null)
            return;

        $this->dealers = array();

        $user_dealers_list = array();
        if ($this->user->getAuthUser()->isRegionalManager()) {
            $userDealers = $this->user->getAuthUser()->hasDealersListFromNaturalPerson();

            foreach ($userDealers as $k => $i) {
                $user_dealers_list[] = $k;
            }
        }

        $query = DealerTable::getVwDealersQuery();
        if (!empty($user_dealers_list)) {
            $query->andWhereIn('d.id', $user_dealers_list);
        }

        foreach ($query->execute() as $dealer) {
            $this->dealers[ $dealer->getId() ] = $dealer;
        }
    }

    function build ()
    {
        $this->stat = array();

        $query = ActivityTable::getInstance()
            ->createQuery('a')
            ->select('a.id, a.start_date, a.end_date, a.custom_date, a.name, a.brief, a.importance')
            ->innerJoin('a.ActivityField af')
            ->orderBy('a.importance DESC, sort DESC, a.id DESC');


        $result = $query->execute();
        foreach ($result as $item) {
            if ($item->getActivityField()->count() > 0) {
                $this->stat[ 'activities' ][] = $item;
            }
        }

        return $this->stat;
    }

    function buildByActivity ()
    {
        $this->stat[ 'dealers' ] = array();
        $this->stat[ 'quarters' ] = array();

        if (empty($this->activity) && count($this->stat[ 'activities' ]) > 0)
            $this->activity = $this->stat[ 'activities' ][ 0 ];

        $query = ActivityFieldsValuesTable::getInstance()->createQuery('v')
            ->select('v.id as fvId, v.dealer_id, v.val, v.field_id, v.q, v.updated_at')
            ->innerJoin('v.ActivityFields f')
            ->where('f.activity_id = ?', $this->activity->getId())
            //->andWhere('v.updated_at != ?', '')
            ->orderBy('v.id ASC');

        if (isset($this->quarter)) {
            $query->andWhere('q = ?', $this->quarter);
        }

        $result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $not_using_importer = false;

        $statistic = $this->activity->getActivityVideoStatistics()->getFirst();
        if ($statistic) {
            $not_using_importer = $statistic->getNotUsingImporter();
        }

        $totalQ = 0;
        foreach ($result as &$item) {
            if (!array_key_exists($item[ 'q' ], $this->stat[ 'quarters' ])) {
                $this->stat[ 'quarters' ][ $item[ 'q' ] ] = $item[ 'q' ];
            }

            if (array_key_exists($item[ 'dealer_id' ], $this->dealers)) {
                $quarter = $item[ 'q' ];
                if ($quarter != 0) {
                    $totalQ++;
                } else {
                    $quarter = D::getQuarter($item[ 'updated_at' ]);
                }

                if (empty($this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'dealer' ])) {
                    $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'dealer' ] = $this->dealers[ $item[ 'dealer_id' ] ];
                }

                if (!isset($this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'values' ])) {
                    $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'values' ] = array();
                }

                if (!in_array($item[ 'field_id' ], $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'values' ])) {
                    $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'values' ][ 'item' ][ $item[ 'field_id' ] ] = $item;
                    $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'values' ][ $item[ 'field_id' ] ] = $item[ 'field_id' ];
                }

                $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'update_date' ] = $item[ 'updated_at' ];
                $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'last_update_date' ] = '';

                $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'status' ] = array( 'status' => ActivityStatisticPreCheckAbstract::CHECK_STATUS_NONE, 'quarter' => 0, 'year' => 0 );

                if ($this->activity->getActivityVideoStatistics()->count() && $this->activity->getActivityVideoStatistics()->getFirst()->getAllowStatisticPreCheck()) {
                    $statistic = $this->activity->getActivityVideoStatistics()->getFirst();

                    //Проверка статуса статистики
                    $statistic_data = ActivityStatisticPreCheckTable::getInstance()->createQuery()
                        ->where('activity_id = ?', $this->activity->getId())
                        ->andWhere('statistic_id = ?', $statistic->getId())
                        ->andWhere('dealer_id = ?', $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'dealer' ]->getId())
                        ->andWhere('quarter = ?', $quarter)
                        ->andWhere('year = ?', $this->year)
                        ->fetchOne();

                    if ($statistic_data) {
                        $current_status = $statistic_data->getIsChecked();
                        $current_status_label = 'Согласовано';

                        if ($current_status != ActivityStatisticPreCheckAbstract::CHECK_STATUS_CHECKED) {
                            $current_status_label = $current_status == ActivityStatisticPreCheckAbstract::CHECK_STATUS_IN_PROGRESS ? 'Отправлено на согласование' : 'Отклонено';
                        }

                        $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'status' ] = array(
                            'current_status' => $current_status,
                            'current_status_label' => $current_status_label,
                            'quarter' => $quarter,
                            'year' => $this->year
                        );
                    }
                }

                //Делаем проверку на дату последнего изменения данных по статистики
                if ($not_using_importer) {
                    $last_update = ActivityStatisticLastUpdatesTable::getInstance()->createQuery()->where('activity_id = ? and dealer_id = ? and statistic_id = ?',
                        array(
                            $this->activity->getId(), $item[ 'dealer_id' ], $statistic->getId()
                        ))->orderBy('id DESC')->fetchOne();

                    if ($last_update) {
                        $this->stat[ 'dealers' ][ $quarter ][ $item[ 'dealer_id' ] ][ 'last_update_date' ] = $last_update->getCreatedAt();
                    }
                }
            }
        }

        $this->stat[ 'totalQ' ] = $totalQ;
    }

    function buildFields ()
    {
        $this->stat[ 'fields' ] = ActivityFieldsTable::getInstance()
            ->createQuery()
            ->where('activity_id = ? and owner = ?', array( $this->activity->getId(), 0 ))->orderBy('id ASC')->execute();
    }

    function getStat ()
    {
        return $this->stat;
    }

    function getYear ()
    {
        return $this->year;
    }

    function getUser ()
    {
        return $this->user;
    }
}
