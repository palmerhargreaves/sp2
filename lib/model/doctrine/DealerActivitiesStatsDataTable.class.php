<?php

/**
 * DealerActivitiesStatsDataTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class DealerActivitiesStatsDataTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object DealerActivitiesStatsDataTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('DealerActivitiesStatsData');
    }

    public static function getDataBy($dealerStatId, $year)
    {
        return self::getInstance()
            ->createQuery('as')
            ->select('activity_id, status, total_completed, dealer_stat_id, id')
            ->leftJoin('as.Activity a')
            ->where('dealer_stat_id = ?', $dealerStatId)
            ->andWhere('year(a.end_date) >= ?', $year)
            ->orderBy('a.id DESC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    public static function getActivityStatus($item) {
        return self::getInstance()
            ->createQuery('as')
            ->select('status, id')
            ->where('dealer_stat_id = ?', $item['dealer_stat_id'])
            ->andWhere('activity_id = ?', $item['activity_id'])
            ->orderBy('as.updated_at DESC')
                ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    public static function getTotalActivitiesCompleted($dealerStatId) {
        $items = self::getInstance()
            ->createQuery('as')
            ->select('activity_id, status, total_completed, dealer_stat_id, id')
            ->leftJoin('as.Activity a')
            ->where('dealer_stat_id = ?', $dealerStatId)
            ->orderBy('a.id DESC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $total = 0;
        foreach($items as $item) {
            if($item['status'] == 'ok') {
                $total++;
            }
        }

        return $total;
    }

    public static function getRawTableName() {
        return "dealer_activities_stats_data";
    }
}