<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.07.2016
 * Time: 11:33
 */

$data = $company_list_data[$filters['filter_by_company']];
if ($activities_tab == 'finished') {
    include_partial('activity/finished', array('activities' => $data['activities']['finished'], 'year' => $data['year'], 'filters' => $filters));
} else if ($activities_tab == 'activities') {
    include_partial('activity/dealerStatistics', array('builders' => $company_list_data, 'dealers_statistics' => $dealers_statistics, 'year' => $data['year'], 'filters' => $filters));
}
