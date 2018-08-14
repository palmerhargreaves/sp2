<?php

require_once dirname(__FILE__).'/../lib/agreement_model_categoriesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/agreement_model_categoriesGeneratorHelper.class.php';

/**
 * agreement_model_categories actions.
 *
 * @package    Servicepool2.0
 * @subpackage agreement_model_categories
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_model_categoriesActions extends autoAgreement_model_categoriesActions
{
    function preExecute() {
        $this->dispatcher->connect('admin.save_object', array($this, 'onSaveObject'));

        parent::preExecute();
    }

    public function onSaveObject(sfEvent $event) {
        $object = $event['object'];

        $object->setIdentifier(Utils::normalize($object->getName()));
        $object->save();
    }

    public function executeReorderTypes(sfWebRequest $request)
    {
        $this->reorderData(json_decode($request->getParameter('data'))->{'types-list'}, 'AgreementModelTypeTable');

        return sfView::NONE;
    }

    public function executeReorderCategoryFields(sfWebRequest $request)
    {
        $this->reorderData(json_decode($request->getParameter('data'))->{'category-fields-list'}, 'AgreementModelFieldTable');

        return sfView::NONE;
    }

    private function reorderData($data, $cls) {
        $ind = 1;
        foreach ($data as $key) {
            if (!empty($key) && is_numeric($key)) {
                $activity = $cls::getInstance()->find($key);
                if ($activity) {
                    $activity->setPosition($ind);
                    $activity->save();
                }

                $ind++;
            }
        }
    }

    public function executeReorderModelCategories(sfWebRequest $request)
    {
        $this->reorderData($request->getParameter('items'), 'AgreementModelCategoriesTable');

        return sfView::NONE;
    }

    protected function buildQuery() {
        $query = parent::buildQuery();

        return $query->andWhere('is_blank = ?', false)->orderBy('position ASC');
    }

    public function executeCategoryFieldSave(sfWebRequest $request)
    {
        $add_save_fields = $request->getParameter('add_save_fields');
        $field_id = $request->getParameter('field_id');

        $added_fields_count = AgreementModelFieldTable::getInstance()->createQuery()->where('field_parent_id = ?', $field_id)->count();
        if ($add_save_fields == $added_fields_count) {
            return sfView::NONE;
        }

        if ($add_save_fields > $added_fields_count) {
            $field_data = AgreementModelFieldTable::getInstance()->createQuery()->where('id = ?', $field_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            if ($field_data) {
                $parent_field_id = $field_data['id'];

                unset($field_data['id']);

                $new_field_data_copy = $field_data;

                $added_fields_count++;
                $start_ind = $added_fields_count;

                for ($ind = 0; $ind <= ($add_save_fields - $added_fields_count); $ind++ ) {
                    $new_field_data = new AgreementModelField();

                    $new_field_data_copy['name'] = $field_data['name'] . ' ' . $start_ind;
                    $new_field_data_copy['identifier'] = $field_data['identifier'] . '_' . $start_ind;
                    $new_field_data_copy['child_field'] = 0;
                    $new_field_data_copy['hide'] = true;
                    $new_field_data_copy['field_parent_id'] = $parent_field_id;

                    $new_field_data->setArray($new_field_data_copy);
                    $new_field_data->save();

                    $start_ind++;
                }

                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array('success' => true, 'msg' => 'Создание полей успешно завершено.')));
            }

        } else {
            if ($add_save_fields == 0) {
                AgreementModelFieldTable::getInstance()->createQuery()->where('field_parent_id = ?', $field_id)->delete()->execute();
            } else {
                $added_fields = AgreementModelFieldTable::getInstance()->createQuery()->select('id')->where('field_parent_id = ?', $field_id)->orderBy('id DESC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                $result = array_chunk($added_fields, $added_fields_count - $add_save_fields);
                if (isset($result[0]) && count($result[0]) > 0) {
                    foreach ($result[0] as $key => $field) {
                        AgreementModelFieldTable::getInstance()->find($field['id'])->delete();
                    }
                }
            }
        }

        return sfView::NONE;
    }

    public function executeMimeTypesList(sfWebRequest $request) {
        $this->category_id = $request->getParameter('id');

        $binded_mime_types_to_category_ids = array_map(function($item) {
                return $item['mime_type_id'];
        },
        AgreementModelCategoriesAllowedMimeTypesTable::getInstance()->createQuery()->select('mime_type_id')->where('category_id = ?', $this->category_id)->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $mime_types_list = array();
        $mime_types = MimeTypesTable::getInstance()->createQuery()->where('status = ?', true)->execute();
        foreach ($mime_types as $type) {
            $mime_types_list[] = array('checked' => in_array($type->getId(), $binded_mime_types_to_category_ids), 'mime_type' => $type);
        }

        $this->mime_types = $mime_types_list;
    }

    public function executeMimeTypeCheck(sfWebRequest $request) {
        $mime_type_id = $request->getParameter('mime_type_id');
        $category_id = $request->getParameter('category_id');
        $add_remove = $request->getParameter('check_action_type') == 1 ? true : false;

        if ($add_remove) {
            $category_mime_type = new AgreementModelCategoriesAllowedMimeTypes();
            $category_mime_type->setArray(array(
                'category_id' => $category_id,
                'mime_type_id' => $mime_type_id
            ));
            $category_mime_type->save();
        } else {
            AgreementModelCategoriesAllowedMimeTypesTable::getInstance()->createQuery()->where('category_id = ? and mime_type_id = ?', array($category_id, $mime_type_id))->delete()->execute();
        }
        
        return sfView::NONE;
    }
}
