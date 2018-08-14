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
class dealers_channels_statisticsActions extends sfActions
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    function executeIndex(sfWebRequest $request)
    {
        $this->years_list = AgreementModelTable::getInstance()->createQuery()->select('year(created_at) year_created')->groupBy('year_created')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
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
            $file_name = $this->$method($request->getParameter('year'), $request->getParameter('quarter'), $request->getParameter('mandatory_activity'), $request->getParameter('category_or_type'), $request->getParameter('data_type'), $request->getParameter('extended_category_info'));
            $this->getResponse()->setContent(json_encode(array('file_name' => $file_name, 'success' => true)));
        } else {
            $this->getResponse()->setContent(json_encode(array('success' => false)));
        }

        return sfView::NONE;
    }

    private function statisticByGeneral($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info = false)
    {
        $dealer_statistics_calc = new DealersStatisticsCalculateByGeneral($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info);
        $dealer_statistics_calc->start();

        return $dealer_statistics_calc->getData();
    }

    private function statisticByDealers($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info = false)
    {
        $dealers_statistics_calc = new DealersStatisticsCalculateByDealers($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info);
        $dealers_statistics_calc->start();

        return $dealers_statistics_calc->getData();
    }

    private function statisticByDealersAndTypes($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info = false) {
        $dealers_statistics_calc = new DealersStatisticsCalculateByDealersAndTypes($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info);
        $dealers_statistics_calc->start();

        return $dealers_statistics_calc->getData();
    }


}
