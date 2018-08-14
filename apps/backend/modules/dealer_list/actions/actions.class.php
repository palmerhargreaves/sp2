<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 * Created by PhpStorm.
 * User: averinbox
 * Date: 26.01.16
 * Time: 15:04
 */
class dealer_listActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $this->city_id = $request->getGetParameter('city');
        $this->search_num = $request->getGetParameter('search_num');
        $this->is_dealer_disabled = !is_null($request->getParameter('ch_dealer_status_disabled')) ? true : false;
        $this->is_dealer_importer = !is_null($request->getParameter('ch_dealer_has_importer')) ? true : false;

        $order = $request->getGetParameter('order');
        if(empty($order))
            $order = 'name';

        $this->direction = $request->getGetParameter('direction');
        if (empty($this->direction))
            $this->direction = 'DESC';


        $Dealer = DealerTable::getInstance()->createQuery()->select()->where('number LIKE ?', '%93500%');//->andWhere('importer_id = 1');

        $q = Doctrine_Query::create()
            ->from('Dealer d')
            ->leftJoin('d.City c')
            ->leftJoin('d.RegionalManager as rm')
            ->leftJoin('d.NfzRegionalManager as nfzrm');

        DealerTable::queryByNumber($q);

//        echo '<pre>'. print_r($q->getSqlQuery(), 1) .'</pre>'; die();

        if ($this->search_num) {
            if (is_numeric($this->search_num)) {
                $q->andWhere('number LIKE ?', '%' . $this->search_num . '%');
            } else {
                $q->andWhere('name LIKE ?', '%' . $this->search_num . '%');
            }
        }

        if ($this->is_dealer_disabled) {
            $q->andWhere('status = ?', false);
        }

        if ($this->is_dealer_importer) {
            $q->andWhere('importer_id = 1');
        }

        if ($this->city_id)
            $q->andWhere('city_id = ?', $this->city_id);

        $this->cities = CityTable::getInstance()->createQuery()->select('id, name')->orderBy('name')->execute();

        if ($order) {
            if($order == 'city_id') {
                $q->orderBy('c.name'. ' ' . $this->direction);
            } elseif($order == 'regional_manager_nfz') {
                $q->orderBy('nfzrm.surname'. ' ' . $this->direction);
            } elseif($order == 'regional_manager_pkw') {
                $q->orderBy('rm.surname'. ' ' . $this->direction);
            }
            else {
                $q->orderBy('d.' . $order . ' ' . $this->direction);
            }
        } else {
            $q->orderBy('d.' . $order . ' ' . $this->direction);
        }

        if($this->direction == 'ASC') { $this->direction = 'DESC'; } else {$this->direction = 'ASC';}
//        echo '<pre>'. print_r($Dealer->getSqlQuery(), 1) .'</pre>'; die();
        $this->dealers = $q->execute();
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->dealer_id = $request->getGetParameter('id');
        $post = $request->getPostParameters();

        $this->dealer = new Dealer();
        $this->cities = CityTable::getInstance()->createQuery()->select('id, name')->execute();
        $this->natural_persons = NaturalPersonTable::getInstance()->createQuery()->select('id, firstname, surname')->where('importer_id = 1')->orderBy('surname, firstname')->execute();
//        echo '<pre>'. print_r($this->natural_persons, 1) .'</pre>'; die();

        if ($this->dealer_id) {
            $this->dealer = DealerTable::getInstance()->findOneById($this->dealer_id);
        }

        $this->dealers_groups = DealersGroupsTable::getInstance()->createQuery()->orderBy('id ASC')->execute();

        if (!empty($post)) {
            if (empty($post['id'])) {

                if (DealerTable::getInstance()->createQuery()->where('number = ?', $post['number'])->count() > 0) {
                    $this->getUser()->setFlash('error', 'Такой номер дилера (' . $post['number'] . ') уже существует в базе.');
                    $this->redirect('/backend.php/dealer_list/edit');
                }
            }

            $this->dealer->setNumber($post['number']);
            $this->dealer->setName($post['name']);
            $this->dealer->setSlug($post['slug']);
            $this->dealer->setAddress($post['address']);
            $this->dealer->setPhone($post['phone']);
            $this->dealer->setSite($post['site']);
            $this->dealer->setEmail($post['email']);
            $this->dealer->setEmailSo($post['email_so']); 
            $this->dealer->setLongitude($post['longitude']);
            $this->dealer->setLatitude($post['latitude']);
            $this->dealer->setLatitude($post['latitude']);
            $this->dealer->setCityId($post['city_id']);
            $this->dealer->setDealerType($post['dealer_type']);

            $this->dealer->setDealerGroupId($post['dealer_group_id']);

            $this->dealer->setOnlySp($post['only_sp']);
            $this->dealer->setNumberLength($post['number_length']);

            $this->dealer->setImporterId(1);
            if (!$post['status']) {
                $this->dealer->setImporterId(0);
            }
            $this->dealer->setStatus($post['status']);

            if ($post['dealer_type'] == Dealer::TYPE_NFZ_PKW) {
                $this->dealer->setNfzRegionalManagerId($post['nfz_regional_manager_id']);
                $this->dealer->setRegionalManagerId($post['regional_manager_id']);
            } else if ($post['dealer_type'] == Dealer::TYPE_PKW) {
                $this->dealer->setRegionalManagerId($post['regional_manager_id']);
                $this->dealer->setNfzRegionalManagerId(0);
            } else {
                $this->dealer->setNfzRegionalManagerId($post['nfz_regional_manager_id']);
                $this->dealer->setRegionalManagerId(0);
            }

            $this->dealer->save();

            $this->getUser()->setFlash('success', 'Запись успешно сохранена.');
            $this->redirect('/backend.php/dealer_list/edit?id=' . $this->dealer->getId());
        }

    }

    public function executeExport(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

        $activity = ActivityTable::getInstance()->find($request->getParameter('activityId'));
        $this->builder = new ActivityStatisticFieldsBuilder(
            array
            (
                'year' => date('Y'),
                'quarter' => -1
            ),
            $activity,
            $this->getUser());

        $stats = $this->builder->getStat();

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle("Дилеры");

        $headers = array("Номер", "Название дилера", "Город", "Телефон", "Адрес", "E-mail", "E-mail SO", "Тип дилера", "Менеджер PKW", "Менеджер NFZ", "Статус");

        $boldLeftFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
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
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        $aSheet->getStyle('A1:K1')->applyFromArray($boldLeftFont);
        $aSheet->getStyle('B:K')->applyFromArray($center);

        $column = 0;
        $tCount = 1;
        foreach ($headers as $head) {
            $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
            $tCount++;
        }

        $aSheet->getRowDimension('1')->setRowHeight(35);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(35);
        }

        $aSheet->getColumnDimension('A')->setWidth(10);

        $fillColor = "f39a95";

        $aSheet->freezePane('A2');

        $row = 3;
        $dealers_list = DealerTable::getInstance()
            ->createQuery()
            ->andWhere('number LIKE ?', '93500%')
            ->orderBy('regional_manager_id ASC, nfz_regional_manager_id ASC, number ASC')
            ->execute();
        foreach ($dealers_list as $dealer_item) {
            $column = 0;

            if (!$dealer_item->getStatus()) {
                $aSheet->getStyle('A' . $row . ':K' . $row)
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($fillColor);
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getShortNumber());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getName());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getCity()->getName());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getPhone());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getAddress());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getEmail());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getEmailSo());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getDealerTypeLabel());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getRegionalManager()
                ? sprintf('%s %s', $dealer_item->getRegionalManager()->getSurname(), $dealer_item->getRegionalManager()->getFirstname())
                : "-"
            );
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_item->getNfzRegionalManager()
                ? sprintf('%s %s', $dealer_item->getNfzRegionalManager()->getSurname(), $dealer_item->getNfzRegionalManager()->getFirstname())
                : ""
            );
            $aSheet->setCellValueByColumnAndRow($column, $row, $dealer_item->getStatus() ? "Опубликован" : "Не опубликован");

            $aSheet->getStyle('A'.$row.':M'.$row)->getAlignment()->setWrapText(true);

            $row++;
        }

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/dealers.xls');

        echo json_encode(array('success' => true, 'exported_dealers_list_url' => '/uploads/dealers.xls'));

        return sfView::NONE;
    }
}
