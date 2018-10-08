<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:26
 */

class activity_consolidated_informationActions extends BaseActivityActions {


    public function executeIndex(sfWebRequest $request) {
        $this->outputActivity($request);
    }


}
