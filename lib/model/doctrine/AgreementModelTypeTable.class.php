<?php

/**
 * AgreementModelTypeTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AgreementModelTypeTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object AgreementModelTypeTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('AgreementModelType');
    }

    public static function getActiveTypesList()
    {
        return self::getInstance()->createQuery()->where('identifier != ?', '');
    }

    public static function getAvailTypes($activity_id)
    {
        $types = array_values(ActivityModelsTypesNecessarilyTable::getInstance()->createQuery()->select('model_type_id')->where('activity_id = ?', $activity_id)->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $result = array_map(function ($type) {
            return $type['model_type_id'];
        }, $types);

        return AgreementModelTypeTable::getInstance()->createQuery('amt')
            ->innerJoin('amt.AgreementModelCategories amc')
            ->where('parent_category_id != ?', 0)
            ->andWhereNotIn('id', $result)
            ->andWhere('amc.is_blank = ?', false)
            ->orderBy('amc.position ASC, amt.position ASC');
    }
}
