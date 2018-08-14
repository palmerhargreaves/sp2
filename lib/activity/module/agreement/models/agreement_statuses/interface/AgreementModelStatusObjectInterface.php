<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.04.2017
 * Time: 12:15
 */

interface AgreementModelStatusObjectInterface
{
    const DEALER = 'dealer';
    const MANAGER_DESIGNER = 'manager_designer';
    const MANAGER = 'manager';
    const SPECIALIST = 'specialist';
    const IMPORTER = 'special_importer';
    const IMPORTER_REPORT = 'special_report_importer';
    const REGIONAL_MANAGER = 'special_regional_manager';
    const REGIONAL_MANAGER_REPORT = 'special_report_regional_manager';

    public function getObject();
}

