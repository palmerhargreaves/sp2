<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 *  models_date actions.
 *
 * @package    Servicepool2.0
 * @subpackage comment_stat
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class dealers_activities_statisticsActions extends sfActions
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    function executeIndex(sfWebRequest $request)
    {

    }

    /**
     * Make unload all dealers data by selected quarter
     * @param sfWebRequest $request
     * @return string
     */
    public function executeUnloadingData(sfWebRequest $request)
    {
        $this->getResponse()->setContentType('application/json');

        $unload_stat_type = $request->getParameter('type');
        $method = 'statistic' . implode("", array_map(function ($item) {
                return ucfirst($item);
            }, explode('_', $unload_stat_type)));;

        if (method_exists($this, $method)) {
            $file_name = $this->$method($request->getParameter('quarter'), $request->getParameter('mandatory_activity'), $request->getParameter('year'), $request->getParameter('consider_next_quarter'));
            $this->getResponse()->setContent(json_encode(array('file_name' => $file_name, 'success' => true)));
        } else {
            $this->getResponse()->setContent(json_encode(array('success' => false)));
        }

        return sfView::NONE;
    }

    private function statisticByYear($quarter, $mandatory_activity, $year = null, $consider_next_quarter = false)
    {
        $dealer_statistics_calc = new DealersStatisticsCalculateByYear($quarter, $mandatory_activity, $year, $consider_next_quarter);
        $dealer_statistics_calc->start();

        return $dealer_statistics_calc->getData();
    }

    private function statisticByQuarters($quarter, $mandatory_activity, $year = null, $consider_next_quarter = false)
    {
        $dealers_statistics_calc = new DealersStatisticsCalculate($quarter, $mandatory_activity, $year, $consider_next_quarter);
        $dealers_statistics_calc->start();

        return $dealers_statistics_calc->getData();
    }


}
