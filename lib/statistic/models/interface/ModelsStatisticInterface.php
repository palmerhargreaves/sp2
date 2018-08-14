<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.11.2016
 * Time: 16:34
 */

interface ModelsStatisticInterface
{
    /**
     * Get models list
     * @return mixed
     */
    public function getModelsList();

    /**
     * Get total amount of models
     * @return mixed
     */
    public function getTotalAmountByModels();
}
