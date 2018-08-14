<?php

/**
 * ActivityExamplesMaterials form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ActivityExamplesMaterialsForm extends BaseActivityExamplesMaterialsForm
{
    public function configure()
    {
        unset($this['created_at']);

        ///$this->widgetSchema['activity_id'] = new sfWidgetFormInputHidden();

        $this->widgetSchema['dealer_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'Dealer', 'query' => Doctrine::getTable('Dealer')->getDealersList(), 'add_empty' => false, 'method' => 'getNameAndNumber'));
        $this->validatorsSchema['dealer_id'] = new sfValidatorDoctrineChoice(array('model' => 'Dealer', 'required' => true));

        $this->widgetSchema['category_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'ActivityExamplesMaterialsCategories', 'query' => Doctrine::getTable('ActivityExamplesMaterialsCategories')->getCategoriesList(), 'add_empty' => false, 'method' => 'getFormattedName'));
        $this->validatorsSchema['category_id'] = new sfValidatorDoctrineChoice(array('model' => 'ActivityExamplesMaterialsCategories', 'required' => true));

        $year = range(2010, date('Y') + 10);
        $years = array_merge(array_combine($year, $year));

        $this->widgetSchema['year'] = new sfWidgetFormChoice(array('choices' => $years));
        $this->validatorSchema['year'] = new sfValidatorChoice(array('required' => true, 'choices' => array_keys($years)));

        $this->widgetSchema['preview_file'] = new WidgetFormFile(array(
            'label' => 'Превью',
            'delete_label' => 'Удалить файл',
            'file_src' => '/uploads/' . ActivityExamplesMaterials::FILE_PREVIEW_PATH
        ));

        $this->validatorSchema['preview_file'] = new sfValidatorFile(array(
            'required' => false,
            'path' => sfConfig::get('sf_upload_dir') . '/' . ActivityExamplesMaterials::FILE_PREVIEW_PATH,
            'validated_file_class' => 'ValidatedFile',
            /*'mime_types' => array(
              'image/jpeg',
              'image/pjpeg',
              'image/gif',
              'image/png',
              'image/x-png',
              'application/pdf',
              'application/postscript',
              'image/vnd.adobe.photoshop',
              'application/cdr',
              'application/coreldraw',
              'application/x-cdr',
              'application/x-coreldraw',
              'image/cdr',
              'image/x-cdr',
              'zz-application/zz-winassoc-cdr',
              'application/zip',
              'application/x-rar-compressed',
              'application/x-rar',
              'application/msword',
              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              'application/vnd.ms-excel',
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/vnd.ms-powerpoint',
              'application/vnd.openxmlformats-officedocument.presentationml.presentation',
              'image/tiff',
              'application/x-shockwave-flash',
              'audio/mpeg',
              'application/octet-stream',
              'video/x-ms-asf',
              'video/x-msvideo',
              'video/x-matroska',
              'video/quicktime',
              'audio/x-ms-wma',
              'video/mp4',
              'video/x-flv',
              'video/x-ms-wmv'
            ),*/
        ));

        $this->widgetSchema['material_file'] = new WidgetFormFile(array(
            'label' => 'Файл',
            'delete_label' => 'Удалить файл',
            'file_src' => '/uploads/' . ActivityExamplesMaterials::FILE_PATH
        ));

        $this->getWidget('category_id')->setAttribute('class', 'example-parent-category');

        $this->validatorSchema['material_file'] = new sfValidatorFile(array(
            'required' => $this->getObject()->isNew() ? true : false,
            'path' => sfConfig::get('sf_upload_dir') . '/' . ActivityExamplesMaterials::FILE_PATH,
            'validated_file_class' => 'ValidatedFile',
            /*'mime_types' => array(
              'image/jpeg',
              'image/pjpeg',
              'image/gif',
              'image/png',
              'image/x-png',
              'application/pdf',
              'application/postscript',
              'image/vnd.adobe.photoshop',
              'application/cdr',
              'application/coreldraw',
              'application/x-cdr',
              'application/x-coreldraw',
              'image/cdr',
              'image/x-cdr',
              'zz-application/zz-winassoc-cdr',
              'application/zip',
              'application/x-rar-compressed',
              'application/x-rar',
              'application/msword',
              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              'application/vnd.ms-excel',
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/vnd.ms-powerpoint',
              'application/vnd.openxmlformats-officedocument.presentationml.presentation',
              'image/tiff',
              'application/x-shockwave-flash',
              'audio/mpeg',
              'application/octet-stream',
              'video/x-ms-asf',
              'video/x-msvideo',
              'video/x-matroska',
              'video/quicktime',
              'audio/x-ms-wma',
              'video/mp4',
              'video/x-flv',
              'video/x-ms-wmv'
            ),*/
        ));

        foreach ($this->validatorSchema->getFields() as $validator) {
            $validator->setMessage('required', 'Обязательно для заполнения');
        }
    }

}
