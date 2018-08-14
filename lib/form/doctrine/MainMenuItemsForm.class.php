<?php

/**
 * MainMenuItems form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MainMenuItemsForm extends BaseMainMenuItemsForm
{
    public function configure()
    {
        $this->widgetSchema['image'] = new WidgetFormFile(array(
            'label' => 'Изображение',
            'delete_label' => 'Удалить файл',
            'file_src' => '/uploads/' . MainMenuItems::FILE_PATH
        ));

        $this->validatorSchema['image'] = new sfValidatorFile(array(
            'required' => false,
            'path' => sfConfig::get('sf_upload_dir') .'/'. MainMenuItems::FILE_PATH,
            'validated_file_class' => 'ValidatedFile',
            'required' => $this->getObject()->isNew() ? true : false
        ));

    }
}
