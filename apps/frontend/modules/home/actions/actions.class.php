<?php

/**
 * home actions.
 *
 * @package    Servicepool2.0
 * @subpackage home
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class homeActions extends sfActions
{
    const SERVICE_DIALOG = 'service';
    const INFO_DIALOG = 'info';

    const FILTER_ACTIVITIES_NAMESPACE = 'activities';
    const FILTER_ACTIVITIES_YEARS_NAMESPACE = 'activities_years';
    const FILTER_ACTIVITIES_STATISTIC_BY_YEAR = 'activities_statistic_by_year';

    private $filter_by_year = null;

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    function executeIndex(sfWebRequest $request)
    {
        //Подтверждение аккаунта
        if ($this->getUser()->getAttribute('approved_by_email', null) != null) {
            $this->approved_by_email = true;
            $this->getUser()->setAttribute('approved_by_email', null);
        }

        $this->getUser()->setAttribute('editor_link', null);

        $this->year = D::getBudgetYear($request);
        $this->budgetYears = D::getBudgetYears($request);

        $this->quarter = $request->getParameter('quarter') ? $request->getParameter('quarter') : D::getQuarter(date('Y-m-d'));

        if (!empty($this->year)) {
            $this->getUser()->setAttribute('filter_by_year', $this->year, self::FILTER_ACTIVITIES_STATISTIC_BY_YEAR);

            $this->filter_by_year = $this->year;
        }

        $this->outputFilterByYear();

        if ($this->getUser()->isDealerUser()) {

            if ($this->getUser()->getAuthUser()->isUserCertificateActive() && !$this->getUser()->getAttribute('msg', false)) {
                $this->getUser()->setAttribute('msg', true);
                $this->redirect('@homepage?msg=yes' . $this->makeReqYear());
            }

            $service = $request->getParameter('service');

            if (!$this->getUser()->getAttribute('steps', false)) {
                $not_completed = ActivityExtendedStatisticStepsTable::checkDealerMustCompleteStatistics($this->getUser()->getAuthUser());

                if (!empty($not_completed)) {
                    $this->getUser()->setAttribute('steps', true);
                    $this->redirect('@homepage?steps=yes');
                }
            }

            //if (DealerServicesDialogsTable::isActiveForUser($this->getUser()->getAuthUser()) && !$this->getUser()->getAttribute('service', false)) {
            if (DealerServicesDialogsTable::isActiveForUser($this->getUser()->getAuthUser()) && empty($service)) {
                $this->getUser()->setAttribute('service', true);
                $this->redirect('@homepage?service=yes' . $this->makeReqYear());
            } else if (DialogsTable::getLastActiveInfoDialog() && !$this->getUser()->getAttribute('info', false)) {
                $this->getUser()->setAttribute('info', true);
                $this->redirect('@homepage?info=yes' . $this->makeReqYear());
            }

        } /*elseif ($this->getUser()->isImporter() && !$this->getUser()->isManager()) {
            $this->redirect('@agreement_module_activities_status');
        }*/

    }

    function executeGenFiles() {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Users');

        $headers = array('Пользователь', 'Привязки После', 'Привязки До');
        $column = 0;
        $row = 0;

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
            )
        );
        $center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        /*$aSheet->getStyle('A1:G1')->applyFromArray($boldFont);
        $aSheet->getStyle('B:G')->applyFromArray($center);*/

        $column = 0;
        $tCount = 1;
        foreach($headers as $head) {


          $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
          $tCount++;
        }

        $aSheet->getColumnDimension('A')->setWidth(50);
        $aSheet->getColumnDimension('B')->setWidth(50);
        $aSheet->getColumnDimension('C')->setWidth(55);


        $row = 3;
        $column = 0;
        $tCount = 1;

        $fillColor = "BB8300";

        $users = UserTable::getInstance()->createQuery()->select()->orderBy('id ASC')->execute();
        foreach ($users as $user) {
            $column = 0;

            $aSheet->setCellValueByColumnAndRow($column++, $row, sprintf('[%s] %s', $user->getId(), $user->getEmail()));
            $dealer_text = '';
            $dealers_ids = '';
            foreach ($user->getDealerUsers() as $dealer) {
                $dealer_text .= sprintf('[%s] %s (%s) | ', $dealer->getDealer()->getShortNumber(), $dealer->getDealer()->getName(), $dealer->getDealer()->getDealerTypeLabel());
                $dealers_ids .= $dealer->getDealer()->getId();
            }
            $aSheet->setCellValueByColumnAndRow($column, $row, $dealer_text);

            $column++;
            $dealer_text = '';
            $dealers_ids_eq = '';
            foreach (DealerUserOldTable::getInstance()->createQuery()->where('user_id = ?', $user->getId())->execute() as $dealer) {
                $dealer_text .= sprintf('[%s] %s (%s) | ', $dealer->getDealer()->getShortNumber(), $dealer->getDealer()->getName(), $dealer->getDealer()->getDealerTypeLabel());
                $dealers_ids_eq .= $dealer->getDealer()->getId();
            }

            $aSheet->setCellValueByColumnAndRow($column, $row, $dealer_text);

            if ($dealers_ids != $dealers_ids_eq) {
                $aSheet->getStyle('A' . $row . ':C' . $row)
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($fillColor);
            }

            $row++;
        }

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/pkw_users_dealers.xls');

        $this->redirect('http://dm.vw-servicepool.ru/uploads/pkw_users_dealers.xls');
    }

    function executeImportUsers(sfWebRequest $request)
    {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Users');

        $headers = array('Дилер', 'Группа', 'Email', 'Имя', 'Фамилия', 'Должность', 'Активен');
        $column = 0;
        $row = 0;

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
            )
        );
        $center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        /*$aSheet->getStyle('A1:G1')->applyFromArray($boldFont);
        $aSheet->getStyle('B:G')->applyFromArray($center);*/

        $column = 0;
        $tCount = 1;
        /*foreach($headers as $head) {


          $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
          $tCount++;
        }*/

        $aSheet->getColumnDimension('A')->setWidth(10);
        $aSheet->getColumnDimension('B')->setWidth(30);
        $aSheet->getColumnDimension('C')->setWidth(35);
        $aSheet->getColumnDimension('D')->setWidth(30);
        $aSheet->getColumnDimension('E')->setWidth(30);
        $aSheet->getColumnDimension('F')->setWidth(30);
        $aSheet->getColumnDimension('G')->setWidth(30);
        $aSheet->getColumnDimension('H')->setWidth(30);
        $aSheet->getColumnDimension('I')->setWidth(30);
        $aSheet->getColumnDimension('J')->setWidth(30);

        $row = 1;
        $column = 0;
        $tCount = 1;

        $users = UserTable::getInstance()->createQuery()->select()->orderBy('id ASC')->execute();
        foreach ($users as $user) {
            $column = 0;

            $dealer = $user->getDealerUsers()->getFirst();
            if (empty($dealer))
                continue;

            $dealer = $dealer->getDealer();

            $aSheet->setCellValueByColumnAndRow($column++, $row, sprintf('%s', $user->getId()));
            $aSheet->setCellValueByColumnAndRow($column++, $row, sprintf('%s %s', $user->getSurname(), $user->getName()));
            $aSheet->setCellValueByColumnAndRow($column++, $row, $user->getEmail());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $user->getPassword());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getName());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getCity()->getName());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $user->getPhone());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getAddress());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getSite());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getPhone());


            $row++;
        }

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/usersDealers.xls');

        $this->redirect('http://dm.vw-servicepool.ru/uploads/usersDealers.xls');
    }

    function executeSpecialAccept(sfWebRequest $request)
    {
        $act = $request->getParameter('act');
        $user = $this->getUser()->getAuthUser();

        if ($act == 'accept') {
            $budget = $request->getParameter('budget');
            $sum = $request->getParameter('sum');

            $user->setSpecialBudgetQuater($budget);
            $user->setSpecialBudgetSumm($sum);
            $user->setSpecialBudgetStatus(1);
            $user->setSpecialBudgetDateOf(date('d-m-Y'));

        } else {
            $user->setSpecialBudgetStatus(2);
            $user->setSpecialBudgetDateOf(date('d-m-Y'));
        }

        $result = array('status' => -1);
        if ($user->save()) {
            $result['status'] = 1;
        }

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array($result)));

        return sfView::NONE;
    }

    function executeSummerSpecial(sfWebRequest $request)
    {
        $user = $this->getUser()->getAuthUser();
        $startDate = $request->getParameter('startDate');
        $endDate = $request->getParameter('endDate');

        $user->setSummerActionStartDate(str_replace('.', '-', $startDate));
        $user->setsummerActionEndDate(str_replace('.', '-', $endDate));

        $result = array('status' => -1);
        if ($user->save())
            $result['status'] = 1;

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array($result)));

        return sfView::NONE;
    }

    function executeSummerServiceAction(sfWebRequest $request)
    {
        $user = $this->getUser()->getAuthUser();
        $dealer = $user->getDealerUsers()->getFirst();

        $startDate = $request->getParameter('startDate');
        $endDate = $request->getParameter('endDate');

        $temp = new DealerUserServiceAction();

        $temp->setUserId($user->getId());
        $temp->setDealerId($dealer->getDealerId());

        $temp->setSummerServiceActionStartDate(str_replace('.', '-', $startDate));
        $temp->setSummerServiceActionEndDate(str_replace('.', '-', $endDate));

        $result = array('status' => -1);
        if ($temp->save())
            $result['status'] = 1;

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array($result)));

        return sfView::NONE;

    }

    function outputBikes()
    {
        $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
        $user = $this->getUser()->getAuthUser();

        $query = "SELECT COUNT(*) as total FROM bikes_dealer WHERE dealer_id = :param";
        $smt = $pdo->prepare($query);
        $smt->execute(array("param" => $user->getId()));
        $result = $smt->fetchAll();

        if (!empty($result) && $result[0]['total'] != 0) {
            $this->bikesFrm1 = null;
        } else {
            $query = "SELECT * FROM bikes WHERE ftype = :param";
            $smt = $pdo->prepare($query);

            $params = array("param" => 1);
            $smt->execute($params);

            $this->bikesFrm1 = $smt->fetchAll();

            $params = array("param" => 2);
            $smt->execute($params);

            $this->bikesFrm2 = $smt->fetchAll();
        }
    }

    function executeBikesAdd(sfWebRequest $request)
    {
        $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
        $user = $this->getUser()->getAuthUser();

        $query = "INSERT INTO bikes_dealer(bike_id, dealer_id, count, date_of_order) VALUES(:param1, :param2, :param3, :param4)";
        $smt = $pdo->prepare($query);

        $values = $request->getParameter('values');
        foreach ($values as $val) {
            $params = array("param1" => $val['id'],
                "param2" => $user->getId(),
                "param3" => $val['value'],
                "param4" => date("d-m-Y H:i:s"));

            $smt->execute($params);
        }

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('status' => true)));

        return sfView::NONE;
    }

    function executeGazetaFile(sfWebRequest $request)
    {
        $file = $this->getUser()->getAuthUser()->getGazetaFiles();

        $dealer = $this->getUser()->getAuthUser()->getDealerUsers()->getFirst();
        if (!$dealer) {
            return true;
        }

        $dealer = DealerTable::getInstance()->createQuery('d')->where('id = ?', $dealer->getDealerId())->fetchOne();
        $dealerNumber = substr($dealer->getNumber(), 5);

        $gazeta = new GazetaFiles();
        $gazeta->setDealerIndex($dealerNumber);
        $gazeta->setFileName($file);

        $gazeta->save();

        $this->redirect('http://dm.vw-servicepool.ru/uploads/gazeta/' . $file);
    }

    function executeGazetaOffsetFile(sfWebRequest $request)
    {
        $file = $this->getUser()->getAuthUser()->getGazetaOffsetFiles();

        $dealer = $this->getUser()->getAuthUser()->getDealerUsers()->getFirst();
        if (!$dealer) {
            return true;
        }

        $this->redirect('http://dm.vw-servicepool.ru/uploads/gazeta/' . $file);
    }

    function executeServiceAction(sfWebRequest $request)
    {
        $user = $this->getUser()->getAuthUser();
        $dealer = $user->getDealerUsers()->getFirst();

        $startDate = $request->getParameter('startDate');
        $endDate = $request->getParameter('endDate');
        $dialogId = $request->getParameter('dialogId');
        $actType = $request->getParameter('actType');

        $temp = new DealersServiceData();

        $temp->setUserId($user->getId());
        $temp->setDealerId($dealer->getDealerId());
        $temp->setDialogServiceId($dialogId);

        if (!empty($startDate) && !empty($endDate)) {
            $temp->setStartDate(str_replace('.', '-', $startDate));
            $temp->setEndDate(str_replace('.', '-', $endDate));
        }

        $temp->setStatus($actType == 'accept' ? 'accepted' : 'declined');
        $temp->save();

        $dialog = DealerServicesDialogsTable::getInstance()->find($dialogId);

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('status' => 1, 'msg' => $dialog->getSuccessMsg())));

        return sfView::NONE;
    }

    function executeAcceptUserPost(sfWebRequest $request)
    {
        $dep = array(1 => 'Отдел сервиса', 2 => 'Отдел маркетинга', 3 => 'Отдел продаж запчастей и аксессуаров', 4 => 'Генеральный директор');

        $department = $request->getParameter('department');
        $post = $department == 4 ? $dep[$department] : $request->getParameter('userPost');

        $user = $this->getUser()->getAuthUser();
        $user->setPost($post);
        $user->save();

        $userPost = new UsersPost();
        $userPost->setDepartment($dep[$department]);
        $userPost->setPost($post);
        $userPost->setUserId($this->getUser()->getAuthUser()->getId());

        $userPost->save();

        return sfView::NONE;
    }

    function executeShowServiceDialog(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $dialogType = $request->getParameter('dialogType');

        if (!empty($dialogType) && self::INFO_DIALOG == $dialogType) {
            $this->data = DialogsTable::getInstance()->find($id);

            $this->dialogType = $dialogType;
        } else {
            $this->data = DealerServicesDialogsTable::getInstance()->find($id);
        }
    }

    private function makeReqYear()
    {
        $url = '';
        if (!empty($this->year)) {
            $url .= "&year=" . $this->year;
        }

        if (!empty($this->quarter)) {
            $url .= '&quarter=' . $this->quarter;
        }

        return $url;
    }

    /**
     * Get activities by type (owned)
     * @return mixed
     */
    private function getActivitiesFilterByOwned()
    {
        $default = $this->getUser()->getAttribute('activity_type_owned', 0, self::FILTER_ACTIVITIES_NAMESPACE);
        $type = $this->getRequestParameter('by_type_owned', $default);

        $this->getUser()->setAttribute('activity_type_owned', $type, self::FILTER_ACTIVITIES_NAMESPACE);

        return $type;
    }

    /**
     * Get activities by type (required)
     * @return mixed
     */
    private function getActivitiesFilterByRequired()
    {
        $default = $this->getUser()->getAttribute('activity_type_required', 0, self::FILTER_ACTIVITIES_NAMESPACE);
        $type = $this->getRequestParameter('by_type_required', $default);

        $this->getUser()->setAttribute('activity_type_required', $type, self::FILTER_ACTIVITIES_NAMESPACE);

        return $type;
    }

    private function getActivitiesFilterByCompany()
    {
        $default = $this->getUser()->getAttribute('activity_company', 1, self::FILTER_ACTIVITIES_NAMESPACE);
        $type = $this->getRequestParameter('filter_by_company', $default);

        $this->getUser()->setAttribute('activity_company', $type, self::FILTER_ACTIVITIES_NAMESPACE);

        return $type;
    }

    private function getActivitiesFilterByStatus()
    {
        $default = $this->getUser()->getAttribute('activity_status', -1, self::FILTER_ACTIVITIES_NAMESPACE);
        $status = $this->getRequestParameter('filter_by_status', $default);

        $this->getUser()->setAttribute('activity_status', $status, self::FILTER_ACTIVITIES_NAMESPACE);

        return $status;
    }

    private function getActivitiesFilterByYear()
    {
        $default = $this->getUser()->getAttribute('activity_year', date('Y'), self::FILTER_ACTIVITIES_YEARS_NAMESPACE);
        $year = $this->getRequestParameter('filter_by_year', $default);

        $this->getUser()->setAttribute('activity_year', $year, self::FILTER_ACTIVITIES_YEARS_NAMESPACE);

        return $year;
    }

    private function getActivitiesFilterBySort()
    {
        /*Sort by field*/
        $default = $this->getUser()->getAttribute('activity_sort_field', 'position', self::FILTER_ACTIVITIES_NAMESPACE);
        $sort_field = $this->getRequestParameter('filter_field_name', !empty($default) ? $default : 'position');

        $this->getUser()->setAttribute('activity_sort_field', $sort_field, self::FILTER_ACTIVITIES_NAMESPACE);

        /*Sort direction (asc, desc)*/
        $default = $this->getUser()->getAttribute('activity_sort_direction', 'asc', self::FILTER_ACTIVITIES_NAMESPACE);
        $sort_direction = $this->getRequestParameter('filter_field_direction', $default);

        $this->getUser()->setAttribute('activity_sort_direction', $sort_direction, self::FILTER_ACTIVITIES_NAMESPACE);

        return array('sort_field' => $sort_field, 'sort_direction' => $sort_direction);
    }

    private function outputActivitiesFilters()
    {
        $this->filter_by_owned = $this->getActivitiesFilterByOwned();
        $this->filter_by_required = $this->getActivitiesFilterByRequired();
        $this->filter_by_company = $this->getActivitiesFilterByCompany();
        $this->filter_by_status = $this->getActivitiesFilterByStatus();
        $this->filter_by_sort = $this->getActivitiesFilterBySort();
        $this->year = $this->getActivitiesFilterByYear();
    }

    public function executeOnFilterActivitiesBy(sfWebRequest $request)
    {
        return $this->outputActivities($request);
    }

    public function outputActivities(sfWebRequest $request, $first_load = false)
    {
        $this->outputActivitiesFilters();

        $year = D::getYear(D::calcQuarterData(time()));
        $by_year = $this->getUser()->getAttribute('current_year', $year, self::FILTER_ACTIVITIES_YEARS_NAMESPACE);
        if (!is_null($by_year)) {
            $this->year = $by_year;
        }

        $this->year = $this->getUser()->getAttribute('filter_by_year', $year, self::FILTER_ACTIVITIES_STATISTIC_BY_YEAR);

        $this->activities_tab = $request->getParameter('activities_tab', '');
        $this->activities_by_company = $request->getParameter('activities_by_company', '');

        $this->filters = array
        (
            'filter_by_owned' => $this->filter_by_owned,
            'filter_by_required' => $this->filter_by_required,
            'filter_by_company' => $this->filter_by_company,
            'filter_by_status' => $this->filter_by_status,
            'filter_by_sort' => $this->filter_by_sort,
            'filter_by_year' => $this->year,
            'filter_by_tab' => $this->activities_tab,
            'filter_activities_by_company' => $this->activities_by_company
        );

        $save_filters = $request->getParameter('save_filters') == 1 ? true : false;
        if (!$save_filters) {
            $company_types_builder = new ActivitiesCompanyTypesBuilder
            (
                $this->getUser(),
                $request,
                $this->filters
            );
            $company_types_builder->build($request->isXmlHttpRequest(), $first_load);

            $this->company_list_data = $company_types_builder->getData();
            $this->dealers_statistics = $company_types_builder->getDealersStatistic();

            if (!empty($this->activities_tab)) {
                $this->setTemplate('onFilterActivitiesByTab');
            }
        } else {
            return sfView::NONE;
        }

    }

    public function executeLoadActivitiesData(sfWebRequest $request)
    {
        $this->outputActivities($request, true);
    }

    public function outputFilterByYear()
    {
        $default = $this->getUser()->getAttribute('current_year', D::getYear(time()), self::FILTER_ACTIVITIES_NAMESPACE);
        $year = $this->getRequestParameter('year', $default);
        $this->getUser()->setAttribute('current_year', $year, self::FILTER_ACTIVITIES_NAMESPACE);

        $this->year = $year;
    }
}
