<?php

/**
 * activities_examples actions.
 *
 * @package    Servicepool2.0
 * @subpackage activity
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activities_examplesActions extends ActionsWithJsonForm
{
    const FILTER_NAMESPACE = 'examples_filter';

    function executeIndex(sfWebRequest $request)
    {
        $this->outputFilters();

        $model = new ActivityExamplesUtils(array('by_year' => $this->getFilterByYear(), 'by_text' => $this->getFilterByText()));
        $model->build();

        $this->examples = $model->getData();

        //var_dump($this->examples);
        //exit;

        $this->years = $model->getYears();
    }

    private function outputFilters() {
        $this->outputYearFilter();
        $this->outputTextFilter();
    }

    private function outputYearFilter() {
        $this->filter_by_year = $this->getFilterByYear();
    }

    private function outputTextFilter() {
        $this->filter_by_text = $this->getFilterByText();
    }

    private function getFilterByYear() {
        $default = $this->getUser()->getAttribute('by_year', Utils::getIndexFromGenList(date('Y')), self::FILTER_NAMESPACE);

        $date = $this->getRequestParameter('activity_examples_filter_by_year', $default);
        $this->getUser()->setAttribute('by_year', $date, self::FILTER_NAMESPACE);

        return $date;
    }

    private function getFilterByText() {
        $default = $this->getUser()->getAttribute('by_test', '', self::FILTER_NAMESPACE);
        $text = $this->getRequestParameter('activity_examples_filter_by_name', $default);
        $this->getUser()->setAttribute('by_test', $text, self::FILTER_NAMESPACE);

        return $text;
    }

    public function executeDownloadFile(sfWebRequest $request)
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

        return $this->sendJson(array('success' => false));
    }
}
