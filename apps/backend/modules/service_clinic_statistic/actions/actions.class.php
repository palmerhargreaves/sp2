<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');


/**
 *  service_clinic_statistic actions.
 *
 * @package    Servicepool2.0
 * @subpackage comment_stat
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class service_clinic_statisticActions extends sfActions
{
    const OK_ICON = 'ok-icon-active.png';
    const BAD_ICON = 'ok-icon.png';

    const OFFSET_X = 85;
    const OFFSET_Y = 3;

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    function executeIndex(sfWebRequest $request)
    {
        $service_clinic_statistic = new ServiceClinicStatisticUtils();

        $this->activities = $service_clinic_statistic->getActivitiesList();
        $this->years = $service_clinic_statistic->getYearsList();
    }

    public function executeExport(sfWebRequest $request)
    {
        $service_clinic_statistic = new ServiceClinicStatisticUtils(
            array
            (
                'activity_id' => $request->getParameter('sb_activity'),
                'year' => $request->getParameter('sb_year'),
                'q' => $request->getParameter('sb_quarter')
            )
        );

        $export_data = $service_clinic_statistic->export();

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Service Clinic Statistics');

        $headers = array('№ дилера', 'Название дилера', 'Дата проведения мероприятия',
            'Срок действия сертификата', 'Концепция согласована', 'Номер заявки', 'Заявка согласована', 'Статистика заполнена', 'Учтена в квартал');

        $column = 0;
        foreach ($headers as $head) {
            $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
        }

        $boldLeftFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $boldFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
        $center = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
        $right = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $right_bold = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $aSheet->getRowDimension('1')->setRowHeight(35);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }


        $fillColor = 'f0fffe';
        //#fff7f0
        //
        $row = 1;

        $aSheet->getColumnDimension('A')->setWidth(10);
        $aSheet->getColumnDimension('B')->setWidth(35);

        $aSheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($center);
        $aSheet->getStyle('A' . $row . ':M' . $row)
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('d5fbd8');

        $row = 2;
        foreach ($export_data['models'] as $dealer_id => $models_list)
        {
            foreach ($models_list as $key => $data)
            {
                if (!is_null($data['concept_data']['concept']))
                {
                    $concept = $data['concept_data']['concept'];

                    $column = 0;
                    $aSheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($boldLeftFont);
                    $aSheet->getRowDimension($row)->setRowHeight(25);

                    $aSheet->getStyle('A' . $row . ':A' . $row)->applyFromArray($right);

                    $dealer = DealerTable::getInstance()->find($concept['c_dealer_id']);

                    $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getShortNumber());
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getName());

                    $dates = AgreementModelDatesTable::getInstance()->createQuery()->select('date_of')->where('model_id = ?', $concept['c_id'])->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                    if ($dates) {
                        $aSheet->setCellValueByColumnAndRow($column++, $row, $dates['date_of']);
                        $aSheet->getStyle('C' . $row . ':C' . $row)->applyFromArray($center);
                    } else {
                        $aSheet->setCellValueByColumnAndRow($column++, $row, '-');
                    }

                    $settings_dates = AgreementModelSettingsTable::getInstance()->createQuery()->select('certificate_date_to')->where('model_id = ?', $concept['c_id'])->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                    if ($settings_dates) {
                        $aSheet->setCellValueByColumnAndRow($column++, $row, $settings_dates['certificate_date_to']);
                        $aSheet->getStyle('D' . $row . ':D' . $row)->applyFromArray($right);
                    } else {
                        $aSheet->setCellValueByColumnAndRow($column++, $row, '-');
                    }

                    if (!empty($data['concept_data']) && isset($data['concept_data']['concept_completed']) && $data['concept_data']['concept_completed']) {
                        Utils::drawExcelImage(self::OK_ICON, 'E' . $row, $pExcel, self::OFFSET_X, self::OFFSET_Y);
                    } else {
                        Utils::drawExcelImage(self::BAD_ICON, 'E' . $row, $pExcel, self::OFFSET_X, self::OFFSET_Y);
                    }
                    $column++;

                    $aSheet->getStyle('E' . $row . ':E' . $row)->applyFromArray($center);

                    $aSheet->setCellValueByColumnAndRow($column++, $row, $data['model']['m_id']);

                    Utils::drawExcelImage($data['model_completed'] ? self::OK_ICON : self::BAD_ICON, 'G' . $row, $pExcel, self::OFFSET_X, self::OFFSET_Y);
                    $column++;

                    $aSheet->getStyle('G' . $row . ':G' . $row)->applyFromArray($center);
                    if (!empty($data['concept_data'])) {
                        Utils::drawExcelImage($data['concept_data']['statistic_completed'] ? self::OK_ICON : self::BAD_ICON, 'H' . $row, $pExcel, self::OFFSET_X, self::OFFSET_Y);
                        $column++;

                        $aSheet->getStyle('H' . $row . ':H' . $row)->applyFromArray($center);

                        $aSheet->setCellValueByColumnAndRow($column++, $row, $data['concept_data']['statistic_completed'] && $data['concept_data']['concept_completed'] && $data['model_completed'] && $data['concept_data']['q'] != -1 ? $data['concept_data']['q'] : ' ');
                        $aSheet->getStyle('I' . $row . ':I' . $row)->applyFromArray($right);
                    }

                    /*$aSheet->getStyle('A' . $row . ':M' . $row)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($fillColor);*/

                    $row++;
                }
            }
        }

        $concept_row = $row += 2;
        $modes_row = $concept_row;

        /*Conceps stats*/
        $aSheet->setCellValueByColumnAndRow(2, $concept_row, 'Всего концепций');
        $aSheet->getStyle('C' . $concept_row . ':C' . $concept_row)->applyFromArray($right_bold);

        $aSheet->setCellValueByColumnAndRow(3, $concept_row++, $export_data['concepts_models_stats']['total_concepts']);

        $aSheet->setCellValueByColumnAndRow(2, $concept_row, 'Выполнено');
        $aSheet->getStyle('C' . $concept_row . ':C' . $concept_row)->applyFromArray($right_bold);

        $aSheet->setCellValueByColumnAndRow(3, $concept_row++, $export_data['concepts_models_stats']['total_concepts_completed']);

        $aSheet->setCellValueByColumnAndRow(2, $concept_row, 'В работе');
        $aSheet->getStyle('C' . $concept_row . ':C' . $concept_row)->applyFromArray($right_bold);

        $aSheet->setCellValueByColumnAndRow(3, $concept_row, $export_data['concepts_models_stats']['total_concepts_in_work']);

        /*Models stats*/
        $aSheet->setCellValueByColumnAndRow(5, $modes_row, 'Всего заявок');
        $aSheet->getStyle('F' . $modes_row . ':F' . $modes_row)->applyFromArray($right_bold);
        $aSheet->setCellValueByColumnAndRow(6, $modes_row++, $export_data['concepts_models_stats']['total_models']);

        $aSheet->setCellValueByColumnAndRow(5, $modes_row, 'Выполнено');
        $aSheet->getStyle('F' . $modes_row . ':F' . $modes_row)->applyFromArray($right_bold);
        $aSheet->setCellValueByColumnAndRow(6, $modes_row++, $export_data['concepts_models_stats']['total_models_completed']);

        $aSheet->setCellValueByColumnAndRow(5, $modes_row, 'В работе');
        $aSheet->getStyle('F' . $modes_row . ':F' . $modes_row)->applyFromArray($right_bold);
        $aSheet->setCellValueByColumnAndRow(6, $modes_row, $export_data['concepts_models_stats']['total_models_in_work']);

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/service_clinic_stats.xls');

        echo json_encode(arraY('success' => true, 'url' => '/uploads/service_clinic_stats.xls'));

        return sfView::NONE;
    }
}
