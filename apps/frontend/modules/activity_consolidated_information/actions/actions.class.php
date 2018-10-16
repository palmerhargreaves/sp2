<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:26
 */

require_once sfConfig::get('sf_lib_dir').'/vendor/autoload.php';

ini_set("magic_quotes_runtime", 0);

class activity_consolidated_informationActions extends BaseActivityActions {


    public function executeIndex(sfWebRequest $request) {
        $this->getConsolidatedInformation($request);
    }

    public function executeFilterData(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->getConsolidatedInformation($request);

        return $this->sendJson(array('content' => get_partial('dealers_information', array('consolidated_information' => $this->consolidated_information))));
    }

    /**
     * Создаем пдф файл
     * @param sfWebRequest $request
     * @return mixed
     */
    public function executeExportConsolidatedInformation(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->getConsolidatedInformation($request);

        $html = array(
            get_partial('activity_template_page1_header', array()),
            get_partial('activity_template_page1_body', array()),
            get_partial('activity_template_page1_bottom', array())
        );

        $file_name = '/consolidated_information/activity_consolidated_information.pdf';

        $css = implode('', array(
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/fonts.css'),
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/css.css')
            ));

        $pdf = new mPDF('C');
        $pdf->WriteHTML($css, 1);
        $pdf->WriteHTML(implode('', $html));
        $pdf->setBasePath(sfConfig::get('app_site_url'));
        $pdf->Output(sfConfig::get('app_downloads_path').$file_name, 'F');

        return $this->sendJson(array('success' => true, 'url' => sfConfig::get('app_site_url').'downloads'.$file_name));
    }

    private function getConsolidatedInformation(sfWebRequest $request) {
        $this->outputActivity($request);
        $this->consolidated_information = new ActivityConsolidatedInformation($this->activity, $request);
    }


}
