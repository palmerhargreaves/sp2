<?php

/**
 * material actions.
 *
 * @package    Servicepool2.0
 * @subpackage material
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class materialActions extends BaseActivityActions
{

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        $this->activity = ActivityTable::getInstance()->find($request->getParameter('activity'));
        $this->forward404Unless($this->activity);


        $builder = new MaterialsListBuilder($this->activity);
        $builder->build($this->getUser()->getAuthUser());
        $this->activities = $builder;
    }

    function executeMaterial(sfWebRequest $request)
    {
        $material = MaterialTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($material);

        $material->markAsViewed($this->getUser()->getAuthUser());

        $data = array(
            'name' => $material->getName(),
            'web_previews' => array(),
            'sources' => array()
        );

        if ($material->getFilePreview()) {
            $preview_file_helper = $material->getPreviewFileNameHelper();
            $data['file_preview'] = array(
                'file' => $material->getFilePreview(),
                'size' => $preview_file_helper->getSize(),
                'smart_size' => $preview_file_helper->getSmartSize(),
                'ext' => $preview_file_helper->getKnownExtensionIf()
            );
        } else {
            $data['file_preview'] = false;
        }

        foreach ($material->getWebPreviews() as $web_preview)
            $data['web_previews'][] = $web_preview->getFile();

        foreach ($material->getSources() as $source) {
            $file_name_helper = $source->getFileNameHelper();
            $data['sources'][] = array(
                'id' => $source->getId(),
                'name' => $source->getName(),
                'file' => $source->getFile(),
                'size' => $file_name_helper->getSize(),
                'smart_size' => $file_name_helper->getSmartSize(),
                'ext' => $file_name_helper->getKnownExtensionIf(),
                'known_ext' => $file_name_helper->getKnownExtension()
            );
        }

        if ($material->getEditorLink())
            $data['editor_link'] = $material->getEditorLink();

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode($data));

        return sfView::NONE;
    }

    public function executeDownload(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $source = MaterialSourceTable::getInstance()->find($id);
        if ($source) {
            /*$count = $source->getDownloads();
            $source->setDownloads(++$count);
            $source->save();*/

            $item = new MaterialDownloads();
            $item->setMaterialId($id);
            $item->setUserId($this->getUser()->getAuthUser()->getId());
            $item->save();

            $filePath = sfConfig::get('app_materials_upload_path') . '/source/' . $source->getFile();
            if (!F::downloadFile($filePath, $source->getFile())) {
                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Файл не найден')));
            }

            //$this->redirect('/uploads/materials/source/'.$source->getFile());
        }

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Файл не найден')));

        return sfView::NONE;
    }

    public function executeSendRequestToNewMaterial(sfWebRequest $request)
    {
        //$model_type = AgreementModelTypeTable::getInstance()->find($request->getParameter('model_type_id'));
        $material_category = MaterialCategoryTable::getInstance()->find($request->getParameter('model_type_id'));
        $send_mail = new ActivityNewMaterialRequestMail($this->getUser()->getAuthUser(),
            array
            (
                'model_type' => $material_category->getName(),
                'material_name' => $request->getParameter('material_name'),
                'material_width_height' => sprintF('%s x %s', $request->getParameter('material_width'), $request->getParameter('material_height')),
                'material_format' => $request->getParameter('material_format'),
                'material_volume' => $request->getParameter('material_volume'),
                'material_required_info' => $request->getParameter('material_required_info'),
                'material_suggestions' => $request->getParameter('material_suggestions'),
            )
        );
        $send_mail->setPriority(1);

        sfContext::getInstance()->getMailer()->send($send_mail);

        return $this->sendJson(array('success' => true));
    }
}
