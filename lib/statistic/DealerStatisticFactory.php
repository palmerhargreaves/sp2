<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.11.2016
 * Time: 16:29
 */

class DealerStatisticFactory {
    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DealerStatisticFactory();
        }

        return self::$_instance;
    }

    /**
     * @param $cls_name
     * @param $params
     * @return null
     */
    public function getDealerStatistic($cls_name, $params) {
        $cls = 'DealerModels'.ucfirst($cls_name);
        if (class_exists($cls)) {
            return new $cls($params);
        }

        return null;
    }
}
