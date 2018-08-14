<?php

/**
 *  mandatory_models actions.
 *
 * @package    Servicepool2.0
 * @subpackage comment_stat
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class mandatory_modelsActions extends ActionsWithJsonForm
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    function executeIndex(sfWebRequest $request)
    {

    }

    public function executeSearchModel(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $model = AgreementModelTable::getInstance()->createQuery()->where('id = ?', $request->getParameter('model_index'))->fetchOne();

        return $this->sendJson(
            array
            (
                'success' => $model ? true : false,
                'content' => get_partial('search_result', array( 'model' => $model ))
            )
        );
    }

    public function executeModelChangeType(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $result = false;
        if ($request->getParameter('id') != 0) {
            $type = AgreementModelTypeTable::getInstance()->createQuery()->where('id = ?', $request->getParameter('type_index'))->fetchOne();
            if ($type) {
                $model = AgreementModelTable::getInstance()->createQuery()->where('id = ?', $request->getParameter('model_index'))->fetchOne();

                if ($model) {
                    $model_type_used = $request->getParameter('id');

                    $model_type = AgreementModelTypeTable::getInstance()->createQuery()->where('id = ?', $model->getModelTypeId())->fetchOne();

                    if ($model->getIsNecessarilyModel() != 0) {
                        ActivityModelsTypesNecessarilyUsedTable::getInstance()->createQuery()->where('dealer_id = ? and activity_id = ? and necessarily_id = ?',
                            array
                            (
                                $model->getDealerId(),
                                $request->getParameter('activity_id'),
                                $model->getIsNecessarilyModel()
                            ))->delete()->execute();
                    }

                    $mandatory_used = ActivityModelsTypesNecessarilyUsedTable::getInstance()->createQuery()->where('dealer_id = ? and activity_id = ? and necessarily_id = ?',
                        array
                        (
                            $model->getDealerId(),
                            $request->getParameter('activity_id'),
                            $model_type_used
                        ))->fetchOne();

                    if (!$mandatory_used) {
                        $mandatory_used = new ActivityModelsTypesNecessarilyUsed();
                    }

                    //Сохраняем данные о используемом обязательном типе
                    $mandatory_used->setDealerId($model->getDealerId());
                    $mandatory_used->setActivityId($model->getActivityId());
                    $mandatory_used->setNecessarilyId($model_type_used);
                    $mandatory_used->save();

                    //Сохраняем данные о обязательной заявке
                    if ($model->getOriginalModelTypeId() == 0) {
                        $model->setOriginalModelTypeId($model->getModelTypeId());
                    }

                    $model->setModelTypeId($type->getId());
                    $model->setModelCategoryId($type->getParentCategoryId());
                    $model->setIsNecessarilyModel($model_type_used);
                    $model->save();

                    $log_entry = new LogEntry();
                    $log_entry->setArray(array(
                        'user_id' => $this->getUser()->getAuthUser()->getId(),
                        'description' => sprintf('Изменен тип заявки: %s на %s', $type->getName(), $model_type->getName()),
                        'object_id' => $model->getId(),
                        'action' => 'mandatory_model_type_changed',
                        'object_type' => 'user',
                        'login' => $this->getUser()->getAuthUser()->getEmail(),
                        'title' => 'Обязательная заявка',
                    ));

                    $log_entry->save();

                    $result = true;
                }
            }
        } else {
            $model = AgreementModelTable::getInstance()->createQuery()->where('id = ?', $request->getParameter('model_index'))->fetchOne();

            if ($model && $model->getOriginalModelTypeId() != 0) {
                if ($model->getIsNecessarilyModel() != 0) {
                    ActivityModelsTypesNecessarilyUsedTable::getInstance()->createQuery()->where('dealer_id = ? and activity_id = ? and necessarily_id = ?',
                        array
                        (
                            $model->getDealerId(),
                            $model->getActivityId(),
                            $model->getIsNecessarilyModel()
                        ))->delete()->execute();
                }

                $type = AgreementModelTypeTable::getInstance()->createQuery()->where('id = ?', $model->getOriginalModelTypeId())->fetchOne();
                if ($type) {

                    $model->setModelTypeId($type->getId());
                    $model->setModelCategoryId($type->getParentCategoryId());
                    $model->setIsNecessarilyModel(0);
                    $model->save();

                    $log_entry = new LogEntry();
                    $log_entry->setArray(array(
                        'user_id' => $this->getUser()->getAuthUser()->getId(),
                        'description' => sprintf('Оригинальный тип заявки:', $type->getName()),
                        'object_id' => $model->getId(),
                        'action' => 'mandatory_model_type_changed',
                        'object_type' => 'user',
                        'login' => $this->getUser()->getAuthUser()->getEmail(),
                        'title' => 'Обязательная заявка',
                    ));

                    $log_entry->save();

                    $result = true;
                }
            }
        }

        return $this->sendJson(
            array
            (
                'success' => $result,
                'content' => get_partial('search_result', array( 'model' => $model )),
                'result_content' => get_partial('change_result', array( 'result' => $result ))
            )
        );
    }
}
