<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.04.2017
 * Time: 11:53
 */

class AgreementModelReportStatus extends ModelReportStatus implements AgreementModelStatusObjectInterface {

    public function getObject() {
        $items = array_map(function($item) {
            return ucfirst($item);
        }, explode('_', $this->class_prefix));

        $cls = 'AgreementModelReport'.implode('', $items).'Status';

        return new $cls($this->params);
    }
}
