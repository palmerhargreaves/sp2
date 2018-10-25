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

        $css = implode('', array(
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/fonts.css'),
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/css.css')
            ));

        $header = get_partial('activity_template_page1_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);
        $page1_html = array(
            $header,
            get_partial('activity_template_page1_body', array('consolidated_information' => $this->consolidated_information)),
            get_partial('activity_template_page1_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/activity_consolidated_information_1.html';
        file_put_contents($file_name, implode('<br/>', $page1_html));

        $header = get_partial('activity_template_page2_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);
        $page2_html = array(
            $header,
            get_partial('activity_template_page2_body', array('consolidated_information' => $this->consolidated_information)),
            get_partial('activity_template_page2_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/activity_consolidated_information_2.html';
        file_put_contents($file_name, implode('<br/>', $page2_html));

        $header = get_partial('activity_template_page3_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);

        $pages = $this->consolidated_information->getDealersPages();
        //Генерация первой странички
        $page3_html = array(
            $header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[1])),
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/activity_consolidated_information_dealer_page_1.html';
        file_put_contents($file_name, implode('<br/>', $page3_html));

        $dealer_header = get_partial('activity_template_page3_dealer_header', array());
        $dealer_header = str_replace('<style></style>', '<style>'.$css.'</style>', $dealer_header);

        //Генерация остальных страниц, кроме последней
        for($page_index = 2; $page_index < count($pages); $page_index++) {
            $dealer_page_html = array(
                $dealer_header,
                get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$page_index])),
                get_partial('activity_template_page3_dealer_bottom', array())
            );
            $file_name = sfConfig::get('app_root_dir').'www/js/pdf/activity_consolidated_information_dealer_page_'.$page_index.'.html';
            file_put_contents($file_name, implode('<br/>', $dealer_page_html));
        }

        //Генерация последней странички
        $pages_count = count($pages);
        $dealer_last_page_html = array(
            $dealer_header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$pages_count])),
            get_partial('activity_template_page3_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/activity_consolidated_information_dealer_page_'.$pages_count.'.html';
        file_put_contents($file_name, implode('<br/>', $dealer_last_page_html));

        return $this->sendJson(array('success' => true, 'url' => sfConfig::get('app_site_url')));
    }

    private function getConsolidatedInformation(sfWebRequest $request) {
        $this->outputActivity($request);
        $this->consolidated_information = new ActivityConsolidatedInformation($this->activity, $request);
    }

}
