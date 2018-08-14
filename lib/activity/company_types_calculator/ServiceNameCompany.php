<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 13.04.2017
 * Time: 15:35
 */

class ServiceNameCompany extends ActivityCompanyCalculator {

    /**
     * Get company percent from main budget
     */
    protected function getCompanyPercent() {
        return $this->_company->getPercent();
    }
}
