<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 13.04.2017
 * Time: 15:35
 */

class PersonalAdvertisementCompany extends ActivityCompanyCalculator {

    protected function getCompanyPercent() {
        return $this->_company->getPercent();
    }
}

