<?php

require_once dirname(__FILE__) . '/../lib/activity_examples_materialsGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/../lib/activity_examples_materialsGeneratorHelper.class.php';

/**
 * activities_examples_materials actions.
 *
 * @package    Servicepool2.0
 * @subpackage activities_examples_materials
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activity_examples_materialsActions extends autoActivity_examples_materialsActions
{
    private $_parent_id = 0;
    private $_request = null;

    const FILTER = 'materials';

    public function preExecute()
    {
        $this->dispatcher->connect('admin.save_object', array($this, 'onSaveObject'));
        //$this->dispatcher->connect('admin.delete_object', array($this, 'onDeleteObject'));

        parent::preExecute();
    }

    function executeIndex(sfWebRequest $request) {
        parent::executeIndex($request);
    }

    protected function buildQuery() {
        $query = parent::buildQuery();

        return $query;
    }

    function executeNew(sfWebRequest $request)
    {
        parent::executeNew($request);

    }

    function onSaveObject(sfEvent $event) {
        $object = $event['object'];

        $preview_files = $object->getPreviewFile();
        if (empty($preview_files)) {
            $material_file_path = sfConfig::get('app_uploads_path').'/'.ActivityExamplesMaterials::FILE_PATH.$object->getMaterialFile();
            $preview_material_file_path = sfConfig::get('app_uploads_path').'/'.ActivityExamplesMaterials::FILE_PREVIEW_PATH.'pre_'.$object->getMaterialFile();

            if (Utils::isImage($material_file_path)) {
                Utils::makeThumbnailFromImage($material_file_path, $preview_material_file_path, 256);

                $object->setPreviewFile('pre_'.$object->getMaterialFile());
                $object->save();
            }
        }
    }

    function redirect($url, $statusCode = 302)
    {
        if ($url == '@activity_examples_materials_new' && $this->form) {
            //$url .= '?activity_id='.$this->form->getValue('activity_id');
        }

        parent::redirect($url, $statusCode);
    }

    function executeDownloadFile(sfWebRequest $request)
    {
        $file_id = $request->getParameter('file_id');

        $material = ActivityExamplesMaterialsTable::getInstance()->find($file_id);
        if ($material) {
            $filePath = sfConfig::get('app_uploads_path'). '/' . ActivityExamplesMaterials::FILE_PATH . $material->getMaterialFile();
            if (!F::downloadFile($filePath, $material->getMaterialFile())) {
                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Error')));
            }
        }

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Error')));

        return sfView::NONE;
    }

    public function executeBatchCopy(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        foreach ($ids as $id) {
            $material = ActivityExamplesMaterialsTable::getInstance()->createQuery()->where('id = ?', $id)->fetchOne(arraY(), Doctrine_Core::HYDRATE_ARRAY);

            unset($material['id']);
            unset($material['preview_file']);
            unset($material['material_file']);

            $new_material = new ActivityExamplesMaterials();
            $new_material->setArray($material);
            $new_material->save();
        }
    }
}
