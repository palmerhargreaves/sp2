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

        $header = get_partial('activity_template_page1_header');
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);
        $html = array(
            $header,
            get_partial('activity_template_page1_body', array()),
            get_partial('activity_template_page1_bottom', array())
        );

        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/activity_consolidated_information.html';

        file_put_contents($file_name, $html);

        exec('node '.sfConfig::get('app_root_dir').'www/js/pdf/html-to-pdf', $result);

        return $this->sendJson(array('success' => true, 'url' => sfConfig::get('app_site_url')));
    }

    private function getConsolidatedInformation(sfWebRequest $request) {
        $this->outputActivity($request);
        $this->consolidated_information = new ActivityConsolidatedInformation($this->activity, $request);
    }

    private function mb_replace($search, $replace, $subject, &$count=0) {
        if (!is_array($search) && is_array($replace)) {
            return false;
        }
        if (is_array($subject)) {
            // call mb_replace for each single string in $subject
            foreach ($subject as &$string) {
                $string = &mb_replace($search, $replace, $string, $c);
                $count += $c;
            }
        } elseif (is_array($search)) {
            if (!is_array($replace)) {
                foreach ($search as &$string) {
                    $subject = mb_replace($string, $replace, $subject, $c);
                    $count += $c;
                }
            } else {
                $n = max(count($search), count($replace));
                while ($n--) {
                    $subject = mb_replace(current($search), current($replace), $subject, $c);
                    $count += $c;
                    next($search);
                    next($replace);
                }
            }
        } else {
            $parts = mb_split(preg_quote($search), $subject);
            $count = count($parts)-1;
            $subject = implode($replace, $parts);
        }
        return $subject;
    }


}
