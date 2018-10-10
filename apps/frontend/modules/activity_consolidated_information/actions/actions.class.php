<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:26
 */

class activity_consolidated_informationActions extends BaseActivityActions {


    public function executeIndex(sfWebRequest $request) {
        $this->getConsolidatedInformation($request);
    }

    public function executeFilterData(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->getConsolidatedInformation($request);

        return $this->sendJson(array('content' => get_partial('dealers_information', array('consolidated_information' => $this->consolidated_information))));
    }

    private function getConsolidatedInformation(sfWebRequest $request) {
        $this->outputActivity($request);
        $this->consolidated_information = new ActivityConsolidatedInformation($this->activity, $request);
    }
}
