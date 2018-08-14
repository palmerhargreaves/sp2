<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 26.12.2017
 * Time: 10:29
 */

class ExcelUtils {

    public static function mergeCell($start, $end, $row) {
        $merge = 'A1:A1';

        if ($start && $end && $row) {
            $start = PHPExcel_Cell::stringFromColumnIndex($start);
            $end = PHPExcel_Cell::stringFromColumnIndex($end);

            $merge = "$start{$row}:$end{$row}";
        }

        return $merge;
    }
}
