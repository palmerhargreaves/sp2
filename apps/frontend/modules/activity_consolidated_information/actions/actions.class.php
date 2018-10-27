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

        //Первая страница
        $this->generateFirstPage($css, 1);

        //Страницы статистики
        $this->generateStatisticPage($css, 2);

        //Страница данных по дилерам
        $this->generateDealersPage($css, 3);

                return $this->sendJson(array('success' => $this->convertHtmlToPdf(), 'url' => sfConfig::get('app_site_url').'pdf/output.pdf'));
    }

    private function convertHtmlToPdf($page_size = '1024px') {
        $page_name = 'activity_consolidated_information_';

        $page_index = 1;
        $images_files_list = array();
        //Генерация картинок с html
        while(1) {
            $file_name = $page_name.$page_index++;
            $file = 'http://dm.vw-servicepool.ru/js/pdf/data/'.$file_name.'.html';
            $local_file = sfConfig::get('app_root_dir') . 'www/js/pdf/data/'.$file_name.'.html';

            if (!file_exists($local_file)) {
                break;
            }

            exec('phantomjs '.sfConfig::get('app_root_dir').'www/js/pdf/rasterize.js ' . $file .' '. sfConfig::get('app_root_dir') . 'www/js/pdf/data/' . $file_name . '.png '.$page_size);
            $images_files_list[] = sfConfig::get('app_root_dir') . 'www/js/pdf/data/' . $file_name . '.png';
        }

        $save_to = sfConfig::get('app_root_dir').'www/pdf/output.pdf';
        unlink($save_to);

        //Генерация пдф с картинок
        exec('convert '.implode(' ', $images_files_list).' '.$save_to);
        if (file_exists($save_to)) {
            return array('success' => true);
        }

        return array('success' => false);
    }

    private function generateFirstPage($css, $page_index) {
        $header = get_partial('activity_template_page1_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);
        $page1_html = array(
            $header,
            get_partial('activity_template_page1_body', array('consolidated_information' => $this->consolidated_information)),
            get_partial('activity_template_page1_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index.'.html';
        file_put_contents($file_name, implode('<br/>', $page1_html));
    }

    private function generateStatisticPage($css, $page_index) {
        $header = get_partial('activity_template_page2_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);

        $page2_html = array(
            $header,
            get_partial('activity_template_page2_body', array('consolidated_information' => $this->consolidated_information)),
            get_partial('activity_template_page2_bottom', array())
        );

        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index.'.html';
        file_put_contents($file_name, implode('<br/>', $page2_html));
    }

    private function generateDealersPage($css, $page_index) {
        $header = get_partial('activity_template_page3_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);

        $pages = $this->consolidated_information->getDealersPages();

        //Генерация первой странички
        $page3_html = array(
            $header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[1])),
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index++.'.html';
        file_put_contents($file_name, implode('<br/>', $page3_html));

        $dealer_header = get_partial('activity_template_page3_dealer_header', array());
        $dealer_header = str_replace('<style></style>', '<style>'.$css.'</style>', $dealer_header);

        //Генерация остальных страниц, кроме последней
        for($index = 2; $index < count($pages); $index++) {
            $dealer_page_html = array(
                $dealer_header,
                get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$index])),
                get_partial('activity_template_page3_dealer_bottom', array())
            );
            $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index++.'.html';
            file_put_contents($file_name, implode('<br/>', $dealer_page_html));
        }

        //Генерация последней странички
        $pages_count = count($pages);
        $dealer_last_page_html = array(
            $dealer_header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$pages_count])),
            get_partial('activity_template_page3_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index.'.html';
        file_put_contents($file_name, implode('<br/>', $dealer_last_page_html));
    }

    private function getConsolidatedInformation(sfWebRequest $request) {
        $this->outputActivity($request);
        $this->consolidated_information = new ActivityConsolidatedInformation($this->activity, $request);
    }

}
