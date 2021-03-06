<?php

/**
 * ActivityEfficiencyParamsTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ActivityEfficiencyParamsTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object ActivityEfficiencyParamsTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('ActivityEfficiencyParams');
    }

    public function getParamsList($activity_id) {
        $params = array_values(ActivityEfficiencyFormulasTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $result = array_map(function($type) {
            return $type['efficiency_param_id'];
        }, $params);

        return self::getInstance()->createQuery()->whereNotIn('id', $result)->orderBy('id ASC');
    }
}