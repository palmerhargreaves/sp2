<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 * comment_stat actions.
 *
 * @package    Servicepool2.0
 * @subpackage comment_stat
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activity_statsActions extends sfActions
{
    const FILTER_NAMESPACE = 'simple';

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    function executeIndex(sfWebRequest $request)
    {
        $this->makeFilterValues();

        $this->activities = ActivityTable::getInstance()->createQuery('a')->select()->orderBy('id DESC')->execute();

        $this->activities_categories = AgreementModelCategoriesTable::getInstance()->createQuery()->where('position > ?', 0)->orderBy('name ASC')->execute();
        if (!empty($this->activity_category_filter)) {
            $this->activities_categories_for_types = AgreementModelCategoriesTable::getInstance()->createQuery()->where('id = ?', $this->activity_category_filter)->orderBy('name ASC')->execute();
        } else {
            $this->activities_categories_for_types = AgreementModelCategoriesTable::getInstance()->createQuery()->where('position > ?', 0)->orderBy('name ASC')->execute();
        }

        $this->models = $this->makeQuery();
    }

    private function makeFilterValues() {
        $this->activity_filter = $this->getRequest()->getParameter('activity_filter');

        $this->activity_category_filter = $this->getRequest()->getParameter('activity_category_filter');
        $this->activity_category_type_filter = $this->getRequest()->getParameter('activity_category_type_filter');

        $this->activity_filter_quarter = $this->getRequest()->getParameter('filter_by_quarter');
        $this->activity_filter_month = $this->getRequest()->getParameter('filter_by_month');
        $this->activity_report_complete = $this->getRequest()->getParameter('report_complete');

        $this->activity_filter_year = $this->getRequest()->getParameter('filter_by_year');
        if (!$this->activity_filter_year) {
            $this->activity_filter_year = date('Y');
        }

        $this->activity_filter_redactor = $this->getRequest()->getParameter('work_in_redactor');
    }

    /**
     * Get models list by user filters
     * @return array|Doctrine_Collection
     */
    private function makeQuery()
    {

        $query = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->select('m.*, r.status, d.number, d.name, m_type.identifier, m_type.name as type_name, model_category.name as category_name')
            ->leftJoin('m.Report r')
            ->leftJoin('m.Dealer d')
            ->leftJoin('m.ModelType m_type')
            ->leftJoin('m.ModelCategory model_category')
            ->orderBy('m.id DESC');

        //Фильтр по активности
        if (!empty($this->activity_filter)) {
            $query->where('activity_id = ?', $this->activity_filter);
        }

        if ($this->activity_filter_month && $this->activity_filter_month != -1) {
            $query->andWhere('m.created_at LIKE ?', '%' . date($this->activity_filter_year . '-' . $this->activity_filter_month) . '%');
        }

        if ($this->activity_filter_redactor) {
            $query->andWhere('m.model_accepted_in_online_redactor = ?', 1);
        }

        if ($this->activity_report_complete) {
            $query->andWhere('m.status = ? and r.status = ?', array('accepted', 'accepted'));
        }

        //Фильтр моделей по выбранной категории
        if (!empty($this->activity_category_filter)) {
            $query->andWhere('m.model_category_id = ?', $this->activity_category_filter);
        }

        //Фильтр моделей по выбранному типу категории
        if (!empty($this->activity_category_type_filter)) {
            $query->andWhere('m.model_type_id = ?', $this->activity_category_type_filter);
        }

        /**
         * If model is completed and must filter by quarter, check this in log table
         */
        if ($this->activity_filter_quarter && $this->activity_report_complete) {
            $models = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            /*$models_ids = array_map(function($item) {
                return $item['id'];
            }, $models);

            $models_to_check = array();
            $models_from_logs = Utils::getModelDateFromLogEntryWithYear($models_ids);
            array_map(function($item) use(&$models_to_check, $models) {
                if (!array_key_exists($item['object_id'], $models_to_check)) {
                    $models_to_check[$item['object_id']] = array('log_item' => $item, 'model' => array_filter($models, function($model_item) use($item) {
                        return $model_item['id'] == $item['object_id'];
                    }));
                }
            }, $models_from_logs);*/

            $check_by_q = $this->activity_filter_quarter;
            $check_by_year = $this->activity_filter_year;

            $models = array_filter($models, function ($model) use($check_by_q, $check_by_year) {
                $model_date = D::calcQuarterData($model['created_at']);

                $calc_q = D::getQuarter($model_date);
                $calc_y = D::getYear($model_date);

                if ($check_by_q == $calc_q && $check_by_year == $calc_y) {
                    return true;
                }

                return false;
            });
        } else {
            if ($this->activity_filter_year) {
                $query->andWhere('year(m.created_at) = ?', $this->activity_filter_year);
            }

            if ($this->activity_filter_quarter) {
                $query->andWhere('quarter(m.created_at) = ?', $this->activity_filter_quarter);
            }

            $models = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        }


        return $models;
    }

    function executeExportToExcel(sfWebRequest $request)
    {
        $this->makeFilterValues();

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle(sprintf('Список заявок по акт. (%s)', $request->getParameter('activity_filter')));

        //Get models list
        $this->models = $this->makeQuery();

        $headers = array('№ дилера', 'Дилер', 'Номер макета', 'Название макета', 'Категория', 'Тип', 'Размер (если есть)', 'Период', 'Дата создания');
        //настройки для шрифтов
        $baseFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            )
        );
        $boldFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );
        $center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        $left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        $header_font_with_underline = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '8',
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $header_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '8',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $aSheet->getRowDimension('1')->setRowHeight(15);
        $aSheet->getStyle('A1:I1')->getAlignment()->setWrapText(true);

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getStyle('A1:' . $last_letter . '1')->applyFromArray($header_font);
        $aSheet->getStyle('A1:I1')->applyFromArray($header_font_with_underline);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }
        $column = 0;
        $tCount = 1;
        foreach ($headers as $head) {
            $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
            $tCount++;
        }

        $aSheet->getColumnDimension('A')->setWidth(10);
        $aSheet->getColumnDimension('B')->setWidth(40);
        $aSheet->getColumnDimension('C')->setWidth(20);
        $aSheet->getColumnDimension('D')->setWidth(50);
        $aSheet->getColumnDimension('E')->setWidth(30);
        $aSheet->getColumnDimension('F')->setWidth(30);
        $aSheet->getColumnDimension('G')->setWidth(20);
        $aSheet->getColumnDimension('H')->setWidth(25);
        $aSheet->getColumnDimension('I')->setWidth(20);

        $row = 2;
        $tCount = 1;
        foreach ($this->models as $model_item) {
            $column = 0;

            $model = $model_item;
            if (is_null($model)) { continue; }

            $dealer = $model['Dealer'];

            $fields = AgreementModelFieldTable::getInstance()->createQuery()->select()->where('model_type_id = ?', $model['model_type_id'])->andWhere('identifier = ? or identifier = ?', array('period', 'size'))->execute();

            $aSheet->setCellValueByColumnAndRow($column++, $row, substr($dealer['number'], -3));
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer['name'], $dealer['number']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $model['id']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $model['name']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $model['ModelCategory']['category_name']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $model['ModelType']['type_name']);

            $val = "";
            foreach ($fields as $field) {
                if ($field->getIdentifier() == 'size') {
                    $value = AgreementModelValueTable::getInstance()->createQuery()->select()->where('model_id = ? and field_id = ?', array($model['id'], $field->getId()))->fetchOne();

                    if ($value)
                        $val = $value->getValue();
                }
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, $val);
            $val = "";
            foreach ($fields as $field) {
                if ($field->getIdentifier() == 'period') {
                    $value = AgreementModelValueTable::getInstance()->createQuery()->select()->where('model_id = ? and field_id = ?', array($model['id'], $field->getId()))->fetchOne();

                    if ($value)
                        $val = $value->getValue();
                }
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, $val);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $model['created_at']);

            $aSheet->getStyle('C' . $row . ':D' . $row)->applyFromArray($center);

            $row++;
        }

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/models.xls');

        $this->redirect('http://dm.vw-servicepool.ru/uploads/models.xls');
    }

    function executeShow(sfWebRequest $request)
    {
        $this->setTemplate('index');
    }


}
