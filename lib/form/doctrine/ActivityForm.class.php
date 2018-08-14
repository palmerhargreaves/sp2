<?php

/**
 * Activity form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ActivityForm extends BaseActivityForm
{
    public function configure()
    {
        unset($this['created_at'], $this['updated_at']);

        $this->widgetSchema['start_date']->setOption('format', '%day%.%month%.%year%');
        $this->widgetSchema['end_date']->setOption('format', '%day%.%month%.%year%');

        $this->getWidgetSchema()->moveField('modules_list', sfWidgetFormSchema::AFTER, 'name');

        $this->widgetSchema['image_file'] = new WidgetFormFile(array(
            'label' => 'Превью',
            'delete_label' => 'Удалить файл',
            'file_src' => '/uploads/' . Activity::FILE_PREVIEW_PATH
        ));

        $this->validatorSchema['image_file'] = new sfValidatorFile(array(
            'required' => false,
            'path' => sfConfig::get('sf_upload_dir') . '/' . Activity::FILE_PREVIEW_PATH,
            'validated_file_class' => 'ValidatedFile',
        ));

        $this->getWidgetSchema()->setPositions(array(
            'id', 'name', 'hide', 'select_activity', 'start_date', 'end_date', 'custom_date', 'brief', 'description', 'efficiency_description',
            'materials_url', 'finished', 'importance', 'has_concept', 'many_concepts', 'is_concept_complete', 'sort', 'modules_list', 'dealers_list',
            'allow_to_all_dealers', 'is_limit_run', 'stats_description', 'is_own', 'allow_extended_statistic', 'allow_certificate', 'allow_special_agreement', 'allow_share_name',
            'type_company_id', 'own_activity', 'required_activity', 'mandatory_activity', 'event_name', 'image_file'
        ));

        $this->widgetSchema['dealers_list'] = new sfWidgetFormDoctrineChoice(array('model' => 'Dealer', 'query' => Doctrine::getTable('Dealer')->getDealersList(), 'add_empty' => false, 'multiple' => true, 'method' => 'getNameAndNumber'));
        $this->validatorsSchema['dealers_list'] = new sfValidatorDoctrineChoice(array('model' => 'Dealer', 'multiple' => true, 'required' => false));

        $this->widgetSchema['type_company_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'ActivityCompanyType', 'query' => Doctrine::getTable('ActivityCompanyType')->getTypesList(), 'add_empty' => false, 'multiple' => false, 'method' => 'getName'));
        $this->validatorsSchema['type_company_id'] = new sfValidatorDoctrineChoice(array('model' => 'ActivityCompanyType', 'multiple' => false, 'required' => true));

        foreach ($this->validatorSchema->getFields() as $validator) {
            $validator->setMessage('required', 'Обязательно для заполнения');
        }
    }


}
