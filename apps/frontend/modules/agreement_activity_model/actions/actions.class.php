<?php

/**
 * agreement_activity_model actions.
 *
 * @package    Servicepool2.0
 * @subpackage agreement_activity_model
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class agreement_activity_modelActions extends BaseActivityActions
{
    const SORT_ATTR = 'sort';
    const SORT_DIRECT_ATTR = 'sort_direct';
    const FILTER_NAMESPACE = 'agreement_filter';

    const MAX_FILES = 10;
    const CONCEPT_INDEX = 10;
    const GETTER = 'get';
    const SETTER = 'set';

    const REDACTOR_KEY = 'Ahtu9vee';

    protected $check_for_module = 'agreement';

    /**
     * Executes index action
     *
     * @param sfWebRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        $this->year = $request->getParameter('year');

        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $this->outputModelsQuarters($request);

        $this->outputActivity($request);
        $this->outputHasConcept($request);
        $this->outputConcept($request);
        $this->outputConceptType();
        $this->outputModels($request);
        $this->outputBlanks($request);
        $this->outputModelTypes();
        $this->outputTaskList($request);
        $this->outputModelTypesFields();

        $this->outputModelCategories($request);
        $this->outputModelCategoriesFields($request);

        $this->outputActivities($request);
        $this->outputFormActivitiesList($request);

        $this->outputDealerFiles();

        $this->statisticQuarter = $request->getParameter('quarter', D::getQuarter(time()));
        $this->modelId = $request->getParameter('model');
    }

    /**
     *
     */
    public function outputModelCategories() {
        $this->model_categories = AgreementModelCategoriesTable::getInstance()->createQuery()->where('is_blank = ? and status = ?', array(false, true))->orderBy('position ASC')->execute();
    }

    /**
     *
     */
    public function outputModelCategoriesFields() {
        $fields = array();
        $db_fields = AgreementModelFieldTable::getInstance()
            ->createQuery('f')
            ->innerJoin('f.AgreementModelCategories c')
            ->orderBy('position ASC')
            ->execute();

        $child_field_ids = array();
        foreach ($db_fields as $field) {
            if (!isset($fields[$field->getParentCategoryId()])) {
                $fields[$field->getParentCategoryId()] = array();
            }

            if (!array_key_exists($field->getId(), $child_field_ids) && !$field->getHide()) {
                $fields[$field->getParentCategoryId()][] = AgreementModelFieldRendererFactory::getInstance()->create($field);
            }

            if ($field->getChildFields()->count() > 0) {
                foreach ($field->getChildFields() as $child_field) {
                    $child_field_ids[$child_field->getId()] = $child_field->getId();

                    $fields[$field->getParentCategoryId()][] = AgreementModelFieldRendererFactory::getInstance()->create($child_field);
                }
            }
        }

        $this->model_categories_fields = $fields;
    }

    /**
     * @param sfWebRequest $request
     */
    function outputActivities(sfWebRequest $request)
    {
        $user = $this->getUser();
        $query = $this->getActivitiesQuery($request);

        $query->andWhere('a.hide = ?', false);
        ActivityTable::checkActivity($user, $query, 1);

        $this->activities = $query->execute();
    }

    /**
     * Output activities list for model forms
     * @param sfWebRequest $request
     */
    public function outputFormActivitiesList(sfWebRequest $request) {
        $query = $this->getActivitiesQuery($request);

        //$query->andWhere('(a.hide = ? or a.hide = ?)', array(false, true));
        //$query->andWhere('(a.hide = ?)', array(false));

        $activities = $query->execute();

        $this->forms_activities = array();

        //Проверка на скрытые активности для дилера
        foreach ($activities as $activity) {
            //Если активность скрыта, проверяем, участвует дилер в акции
            if ($activity->getHide()) {
                if (DealersServiceDataTable::getInstance()->createQuery('ds')
                    ->innerJoin('ds.Dialog d')
                    ->where('ds.dealer_id = ? and ds.status = ?', array($this->getUser()->getAuthUser()->getDealer()->getId(), 'accepted'))
                    ->andWhere('d.activity_id = ?', $activity->getId())
                    ->count() == 0) {
                    continue;
                }
            }

            $this->forms_activities[] = $activity;
        }
    }

    /**
     * Get activities list by request
     * @param sfWebRequest $request
     * @return Doctrine_Query
     */
    private function getActivitiesQuery(sfWebRequest $request) {
        $user = $this->getUser();
        $show_hidden = $user->isAdmin() || $user->isImporter() || $user->isManager();

        $query = ActivityTable::getInstance()
            ->createQuery('a')
            ->select('a.id, a.start_date, a.end_date, a.custom_date, a.name, a.brief, a.importance, v.id is_viewed')
            ->leftJoin('a.UserViews v WITH v.user_id=?', $this->getUser()->getAuthUser()->getId())
            ->orderBy('a.importance DESC, sort DESC, a.id DESC');

        if ($request->getParameter('year')) {
            $query->andWhere('(a.start_date LIKE ? or a.end_date LIKE ?)', array($this->year . '%', $this->year . '%'));
        } else
            $query->where('a.finished=?', false);

        return $query;
    }

    /**
     * @param sfWebRequest $request
     */
    function executeActivities(sfWebRequest $request)
    {
        $this->outputDealerModels($request);

        $this->outputFilter();

        //$this->executeIndex($request);

    }

    /**
     *
     */
    function outputDealerModels()
    {
        $sorts = array(
            'id' => 'm.id',
            'dealer' => 'm.dealer_id', // сортировка по id дилеров (фактически - это группировка)
            'name' => 'm.name',
            'cost' => 'm.cost'
        );

        $by_year = null;

        $sort_column = $this->getSortColumn();
        $sort_direct = $this->getSortDirection();

        $sql_sort = 'm.id DESC';
        if (isset($sorts[$sort_column]))
            $sql_sort = $sorts[$sort_column] . ' ' . ($sort_direct ? 'DESC' : 'ASC');

        $query = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Activity a')
            ->innerJoin('m.ModelType mt WITH mt.concept=?', false)
            ->innerJoin('m.Dealer dealer')
            //->innerJoin('m.Values am_v')
            ->leftJoin('m.Discussion d')
            ->leftJoin('m.Report r')
            ->orderBy($sql_sort);

        $query->andWhere('m.dealer_id=? and m.is_deleted = ?', array($this->getUser()->getAuthUser()->getDealer()->getId(), false));

        if ($this->getModelFilter()) {
            $query->andWhere('m.id = ? and m.dealer_id = ?', array($this->getModelFilter(), $this->getUser()->getAuthUser()->getDealer()->getId()));
        } else {
            if ($this->getModelStatusFilter() && $this->getModelStatusFilter() != "default") {
                switch ($this->getModelStatusFilter()) {
                    case 'current':
                        $query->andWhere('m.status != ? or m.report_id is null', 'accepted');
                        break;

                    case 'process_draft':
                        $query->andWhere('m.status = ? or m.status = ?', array('declined', 'not_sent'))
                            ->andWhere('m.report_id is null');
                        break;

                    case 'complete':
                        if ($this->getReportStatusFilter() && $this->getReportStatusFilter() == "default") {
                            $query->andWhere('m.status = ? and (r.status != ? or m.report_id is null)', array('accepted', 'accepted'));
                        } else {
                            $query->andWhere('m.status = ?', array('accepted'));
                        }
                        break;
                }
            }

            if ($this->getReportStatusFilter() && $this->getReportStatusFilter() != "default" && ($this->getModelStatusFilter() == 'complete' || $this->getModelStatusFilter() == 'default')) {
                switch ($this->getReportStatusFilter()) {
                    case 'current':
                        $query->andWhere('m.status = ? and (r.status != ? or m.report_id is null)', array('accepted', 'accepted'));
                        break;

                    case 'complete':
                        $query->andWhere('r.status = ?', array('accepted'));
                        break;
                }
            }

            if ($this->getStartDateFilter()) {
                $query->andWhere('m.created_at>=?', D::toDb($this->getStartDateFilter()));
            }

            if ($this->getEndDateFilter()) {
                $query->andWhere('m.created_at<=?', D::toDb($this->getEndDateFilter()));
            }

            if ($this->getModelReportStatusByYearFilter() && $this->getModelReportStatusByYearFilter() != 'default') {
                $query->andWhereIn('year(m.created_at)', $this->getModelReportStatusByYearFilter());
            } else {
                $by_year = D::getYear(D::calcQuarterData(time()));
            }
        }

        $this->models = $this->getModelsListByYear($query, $by_year);
    }

    /**
     * @param $query
     * @param null $by_year
     * @return array
     */
    function getModelsListByYear($query, $by_year = null)
    {
        /*if (!is_null($by_year) && !$this->getModelFilter()) {
            $query->andWhere('(year(created_at) = ? or year(created_at) = ?)', array($by_year, $by_year - 1));
        }*/
        $this->models = $query->execute();

        $isDealer = $this->getUser()->isDealerUser();
        if ($this->getUser()->isImporter()) {
            $isDealer = false;
        }

        $mods = array();
        $result = array();

        $models_ids = array();
        $models_list = arraY();

        foreach ($this->models as $m) {
            $models_ids[] = $m->getId();
            $models_list[$m->getId()] = $m;
        }

        if (count($models_ids) > 0) {
            $models_log_dates = Utils::getModelDateFromLogEntryWithYear($models_ids);
            foreach ($models_log_dates as $model_date) {
                if (array_key_exists($model_date['object_id'], $models_list)) {
                    $model = $models_list[$model_date['object_id']];
                    $maked_date = $model->isCompleted() ? D::calcQuarterData($model_date['created_at']) : D::toUnix(D::makePlusDaysForModel($model, $model_date['created_at']));

                    $mods[$maked_date] = array('model' => $model, 'model_date' => $model_date);
                    unset($models_list[$model_date['object_id']]);
                }
            }

            $models_list = array_filter($models_list);
            foreach ($models_list as $key => $model) {
                if ($isDealer) {
                    $maked_date = D::toUnix($m->getModelAcceptToDate($isDealer));
                } else {
                    $maked_date = D::toUnix(D::makePlusDaysForModel($model, $model->getCreatedAt()));
                }

                $mods[$maked_date] = array('model' => $model, 'model_date' => array());
            }

            ksort($mods, SORT_NUMERIC);
            foreach ($mods as $key => $data) {
                $model = $data['model'];

                $model_label = date('H:i d-m-Y', $key);
                $end_time_work = $model->isOutOfDate();
                if ($this->getModelStatusFilter() == 'blocked' && !$end_time_work) {
                    continue;
                } else if (($this->getModelStatusFilter() == 'blocked' || $this->getModelStatusFilter() == 'default') && $end_time_work && !$model->getAllowUseBlocked()) {
                    $model_label = "Заблокирована";
                } else if ($end_time_work) {
                    continue;
                }

                $date = $model->isModelCompleted() ? $key : $model->getCreatedAt();

                $year = D::getYear($date);
                $prevYear = D::isPrevYear($date);

                $status = $model->isModelCompleted();
                $result[$year]['data'][] = array
                (
                    'date' => $key,
                    'model' => $model,
                    'status' => $status ? $prevYear : false,
                    'label' => $model_label,
                    'end_time_work' => $end_time_work
                );

                if ($status) {
                    $result[$year]['summ'] = isset($result[$year]['summ']) ? $result[$year]['summ'] + $model->getCost() : $model->getCost();
                }
            }
        }

        return $result;
    }

    /**
     *
     */
    function outputActivitystatusFilter()
    {
        $this->activity_status = $this->getActivityStatusFilter();
    }

    /**
     *
     */
    function outputStartDateFilter()
    {
        $this->start_date_filter = $this->getStartDateFilter();
    }

    /**
     *
     */
    function outputEndDateFilter()
    {
        $this->end_date_filter = $this->getEndDateFilter();
    }

    /**
     *
     */
    function outputModelsStatusFilter()
    {
        $this->model_status_filter = $this->getModelStatusFilter();
    }

    /**
     *
     */
    function outputReportStatusFilter()
    {
        $this->report_status_filter = $this->getReportStatusFilter();
    }

    /**
     *
     */
    function outputModelsYearStatusFilter()
    {
        $this->model_report_year_filter = $this->getModelReportStatusByYearFilter();
    }

    /**
     * @return null|string
     */
    function getModelStatusFilter()
    {
        return $this->getModelReportStatusByField('model_status');
    }

    /**
     * @return null|string
     */
    function getReportStatusFilter()
    {
        return $this->getModelReportStatusByField('report_status');
    }

    /**
     * @return null|string
     */
    function getModelReportStatusByYearFilter() {
        return $this->getModelReportStatusByField('model_report_year');
    }

    /**
     * @return mixed|string
     */
    function getModelFilter()
    {
        $default = $this->getUser()->getAttribute('model', '', self::FILTER_NAMESPACE);
        $model_id = $this->isReset ? $default : $this->getRequestParameter('model', $default);
        $this->getUser()->setAttribute('model', $model_id, self::FILTER_NAMESPACE);

        return $model_id;
    }

    /**
     * @param $field
     * @param null $default_value
     * @return null|string
     */
    private function getModelReportStatusByField($field, $default_value = null) {
        $default = $this->getUser()->getAttribute($field, 'default', self::FILTER_NAMESPACE);

        if ($field == 'model_report_year') {
            $status = $this->getRequestParameter($field);
        } else {
            $status = $this->getRequestParameter($field, $default);
        }

        if (!is_null($default_value)) {
            $status = $default_value;
        }

        $this->getUser()->setAttribute($field, $status, self::FILTER_NAMESPACE);

        return $status;
    }

    /**
     * @return string
     */
    function getActivityStatusFilter()
    {
        $default = $this->getUser()->getAttribute('activity_status', 'current', self::FILTER_NAMESPACE);
        $status = $this->getRequestParameter('activity_status', $default);
        $this->getUser()->setAttribute('activity_status', $status, self::FILTER_NAMESPACE);

        return $status;
    }

    /**
     * @return bool|false|int
     */
    function getStartDateFilter()
    {
        return $this->getDateFilter('start_date');
    }

    /**
     * @return bool|false|int
     */
    function getEndDateFilter()
    {
        return $this->getDateFilter('end_date');
    }

    /**
     * @param $name
     * @return bool|false|int
     */
    protected function getDateFilter($name)
    {
        $default = $this->getUser()->getAttribute($name, '', self::FILTER_NAMESPACE);
        $date = $this->getRequestParameter($name, $default);
        $this->getUser()->setAttribute($name, $date, self::FILTER_NAMESPACE);

        return preg_match('#^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$#', $date)
            ? D::fromRus($date)
            : false;
    }

    /**
     *
     */
    function outputFilter()
    {
        $this->outputStartDateFilter();
        $this->outputEndDateFilter();

        $this->outputModelsStatusFilter();
        $this->outputReportStatusFilter();
        $this->outputModelsYearStatusFilter();

        $this->outputModelFilter();

        //$this->outputActivitystatusFilter();
    }

    /**
     * @param sfWebRequest $request
     */
    function executeSort(sfWebRequest $request)
    {
        $column = $request->getParameter('sort', 'id');
        $cur_column = $this->getSortColumn();
        $direction = $this->getSortDirection();

        if ($column == $cur_column) {
            $direction = !$direction;
        } else {
            $direction = false;
            $cur_column = $column;
        }

        $this->setSortColumn($cur_column);
        $this->setSortDirection($direction);

        $this->redirect('@agreement_module_models?activity=' . $this->getActivity($request)->getId());
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeAdd(sfWebRequest $request)
    {
        $draft = $request->getParameter('draft', 'false') == 'true';
        $blank_id = $request->getParameter('blank_id');
        $model_type_id = $request->getParameter('model_type_id');
        $model_category_id = $request->getParameter('model_category_id', 0);
        $period = $request->getParameter('period');
        $task_id = $request->getParameter('task_id');

        if ($blank_id) {
            $blank = AgreementModelBlankTable::getInstance()->find($blank_id);
            // существование болванки проверит валидатор формы
            if ($blank)
                $model_type_id = $blank->getModelTypeId();
        }

        $no_model_changes = $request->getParameter('no_model_changes');
        $no_model_changes = is_null($no_model_changes) ? false : true;

        $model_accepted_in_online_redactor = $request->getParameter('model_accepted_in_online_redactor');
        $model_accepted_in_online_redactor = is_null($model_accepted_in_online_redactor) ? false : true;

        $model_necessarily_id = $request->getParameter('necessarily_id');

        //Model necessarily
        if (empty($model_necessarily_id) || $model_necessarily_id == 0) {
            if ($necessarily_item = ActivityModelsTypesNecessarilyTable::getInstance()->createQuery()->where('activity_id = ? and model_type_id = ? and activity_task_id = ?',
                    array(
                        $this->getActivity($request)->getId(),
                        $model_type_id,
                        $task_id
                    ))->fetchOne()
            ) {
                $model_necessarily_id = $necessarily_item->getId();
            }
        }

        //return $this->sendJson(array($datesFields));
        $form = new AgreementModelForm($draft, null, array(), null, $this->getUser()->getAttribute('editor_link') ? true : false);

        $is_model_scenario_record = false;;
        $upload_files_ids = $request->getPostParameter('upload_files_ids');

        if ($model_category_id != 0) {
            $model_type = AgreementModelTypeTable::getInstance()->find($model_type_id);
            if ($model_type) {
                $is_model_scenario_record = $model_type->getAgreementType() == 'simple' ? false : true;
            }
        } else {
            //Обратная совместимость
            $is_model_scenario_record = ($model_type_id == 2 || $model_type_id == 4);
        }

        if (!$this->getUser()->getAttribute('editor_link')) {
            if ($no_model_changes && $is_model_scenario_record) {
                $upload_files_records_ids = $request->getPostParameter('upload_files_records_ids');

                if (empty($upload_files_ids)) {
                    $form->getValidator('is_valid_data')->setOption('required', true);
                }

                if (empty($upload_files_records_ids)) {
                    $form->getValidator('is_valid_data')->setOption('required', true);
                }
            } else {
                if (empty($upload_files_ids)) {
                    $form->getValidator('is_valid_data')->setOption('required', true);
                }
            }
        }

        $form->bind(
            array(
                'activity_id' => $this->getActivity($request)->getId(),
                'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                'name' => $request->getParameter('name'),
                'blank_id' => $blank_id,
                'model_type_id' => $model_type_id,
                'model_category_id' => $model_category_id,
                'task_id' => $task_id,
                'target' => $request->getParameter('target'),
                'cost' => $request->getParameter('cost'),
                'period' => $period,
                'status' => $draft ? 'not_sent' : 'wait',
                'accept_in_model' => $request->getParameter('accept_in_model'),
                'no_model_changes' => $no_model_changes,
                'model_accepted_in_online_redactor' => $model_accepted_in_online_redactor,
                'editor_link' => $this->getUser()->getAttribute('editor_link') ? $this->getUser()->getAttribute('editor_link') : '',
                'share_name' => $request->getParameter('share_name') ? $request->getParameter('share_name') : ''
            ),
            array()
        );

        $hasEditorLink = false;
        if ($form->isValid()) {
            $form->save();
            $model = $form->getObject();

            //Save user agent
            $model->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $model->setFilesIds(!empty($upload_files_ids) ? $upload_files_ids : 0);
            $model->save();

            //При согласовании концепции, если у активности стоит галочка проверки только импортером устанавливаем флаг
            if ($model->isConcept() && $this->getActivity($request)->getAllowAgreementByOneUser()) {
                $model->setAgreementByImporter(true);
                $model->save();
            }

            //Для спец. согласование, делаем проверку на добавляемую заявку, если это концепция, делаем отметку в концепции
            if ($model->isConcept() && $this->getActivity($request)->getAllowSpecialAgreement()) {
                $model->setSpecialAgreement(true);
                $model->save();
            }

            /**
             * Save uploaded files before check for model statuses
             */
            if (!$this->getUser()->getAttribute('editor_link')) {
                if (!$model->isModelScenario()) {
                    UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_ids, $this->getActivity($request)->getId())->saveFiles();
                } else if ($no_model_changes && $model->isModelScenario()) {
                    UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_ids, $this->getActivity($request)->getId(), 'Scenario')->saveFiles();
                    UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_records_ids, $this->getActivity($request)->getId(), 'Record')->saveFiles();
                } else if ($model->isModelScenario()) {
                    UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_ids, $this->getActivity($request)->getId(), 'ScenarioRecord')->saveFiles();
                }
            } else {
                UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), null, $this->getActivity($request)->getId())->saveFiles();
            }

            //$this->saveModelFiles($model, $upload_files_ids);

            if ($this->getActivity($request)->getAllowCertificate() && !$model->isConcept()) {
                $model->setConceptId($request->getParameter('concept_id'));
                $model->save();
            }

            if ($model->isConcept() && ($this->getActivity($request)->getAllowCertificate() || $this->getActivity($request)->getAllowSpecialAgreement())) {
                $this->addDatesPeriodAction($request, $model);
            }

            $this->updateModelValuesByType($model, $request);

            if (!$no_model_changes && ($model->isModelScenario())) {
                $model->setStep1('wait');
                $model->setModelRecordFile('');
                $model->save();
            }

            /*Обязательная заявка*/
            if ($model_necessarily_id != 0) {
                $model->setIsNecessarilyModel($model_necessarily_id);
                $model->save();

                $necc_used = new ActivityModelsTypesNecessarilyUsed();
                $necc_used->setArray(
                    array
                    (
                        'activity_id' => $this->getActivity($request)->getId(),
                        'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                        'necessarily_id' => $model_necessarily_id
                    )
                );
                $necc_used->save();
            }

            //$this->setModelChanges($model, $model_type_id, $no_model_changes);
            $accept_utils = new AgreementModelAcceptByDealer(array(
                'model' => $model,
                'user' => $this->getUser()->getAuthUser()
            ));

            if (!$draft) {
                $message = $accept_utils->agreement();
            } else {
                $accept_utils->agreementDraft();
            }

            //If we have external url we set to null this url
            if ($this->getUser()->getAttribute('editor_link')) {
                $hasEditorLink = true;
            }
        }

        $message_data = null;
        /*if ($message) {
            $message_data = Utils::formatMessageData($message);
        }*/

        return $this->sendFormBindResult($form, $model_category_id != 0
            ? 'agreement_model_with_category_form.onResponse' : 'agreement_model_form.onResponse',
            $hasEditorLink ? url_for('@agreement_module_models?activity=' . $this->getActivity($request)->getId()) : '',
            $message_data);
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeUpdate(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if (!$model) {
            return $this->sendJson(array('success' => false, 'error' => 'not_found'), 'agreement_model_form.onResponse');
        }

        if ($model->getStep1() != 'accepted') {
            if ($model->getStatus() != 'not_sent' && $model->getStatus() != 'declined')
                return $this->sendJson(array('success' => false, 'error' => 'wrong_status'),
                    $model->getModelCategoryId() != 0 ? 'agreement_model_with_category_form.onResponse' : 'agreement_model_form.onResponse');
        }

        $is_concept = $model->isConcept();

        $draft = $request->getParameter('draft', 'false') == 'true';

        $no_model_changes = $request->getParameter('no_model_changes');
        $no_model_changes = is_null($no_model_changes) ? false : true;

        /*$model_accepted_in_online_redactor = $request->getParameter('model_accepted_in_online_redactor');
        $model_accepted_in_online_redactor = is_null($model_accepted_in_online_redactor) ? false : true;*/

        $model_type_id = $model->getModelType()->getId();
        $blank_id = $request->getParameter('blank_id');
        if (!$blank_id) {
            $model_type_id = $request->getParameter('model_type_id');
        }

        $period = $request->getParameter('period') != null ? $request->getParameter('period') : '' ;

        $form = new AgreementModelForm($draft, $model);
        /**
         * Check is set options No model changes
         */
        $uploaded_files = $model->getUploadedFilesCount($model_type_id);

        $upload_files_ids = $request->getPostParameter('upload_files_ids');
        $upload_files_records_ids = array();

        if ($no_model_changes && $model->isModelScenario($model_type_id)) {
            $upload_files_records_ids = $request->getPostParameter('upload_files_records_ids');

            if (empty($upload_files_ids) && $uploaded_files[AgreementModel::BY_SCENARIO] == 0) {
                $form->getValidator('is_valid_data')->setOption('required', true);
            }

            if (empty($upload_files_records_ids) && $uploaded_files[AgreementModel::BY_RECORD] == 0) {
                $form->getValidator('is_valid_data')->setOption('required', true);
            }
        } else {
            if ($model->isModelScenario($model_type_id)) {
                if ($model->getStep1() == 'accepted') {
                    $upload_files_ids = $request->getPostParameter('upload_files_records_ids');
                    if (empty($upload_files_ids) && $uploaded_files[AgreementModel::BY_RECORD] == 0) {
                        $form->getValidator('is_valid_data')->setOption('required', true);
                    }
                } else {
                    if (empty($upload_files_ids) && $uploaded_files[AgreementModel::BY_SCENARIO] == 0) {
                        $form->getValidator('is_valid_data')->setOption('required', true);
                    }
                }
            } else {
                if (empty($upload_files_ids) && $uploaded_files[AgreementModel::UPLOADED_FILE_MODEL] == 0) {
                    $form->getValidator('is_valid_data')->setOption('required', true);
                }
            }
        }

        $form->bind(
            array(
                //'activity_id' => $this->getActivity($request)->getId(),
                'activity_id' => $request->getParameter('activity_id'),
                'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                'name' => $request->getParameter('name'),
                'blank_id' => $model->getBlankId(),
                'model_type_id' => $model->isConcept() ? AgreementModel::CONCEPT_TYPE_ID : $model_type_id,
                'model_category_id' => $request->getParameter('model_category_id'),
                'period' => $period,
                'task_id' => $model->getTaskId(),
                'target' => $request->getParameter('target'),
                'cost' => $request->getParameter('cost'),
                'status' => $draft ? 'not_sent' : 'wait',
                'accept_in_model' => $request->getParameter('accept_in_model'),
                'no_model_changes' => $no_model_changes,
                //'model_accepted_in_online_redactor' => $model_accepted_in_online_redactor,
                'share_name' => $request->getParameter('share_name') ? $request->getParameter('share_name') : ''
            ), array()
            //$this->getModelFiles($request)
        );

        if ($form->isValid()) {
            $params = array();

            $params[] = sprintf('Категория: %s', $request->getParameter('model_category_id'));
            $params[] = sprintf('Тип категории: %s', $model_type_id);
            $object_params_str = implode('<br/>', $params);

            $form->save();
            $model = $form->getObject();

            //Тупая проверка на принадлежность заявки к концепции, по непонятной причине при отправки от дилера меняется тип заявки
            if ($is_concept) {
                $model->setModelTypeId(10);
                $model->setModelCategoryId(11);
                $model->save();
            }

            //Для спец. согласование, делаем проверку на добавляемую заявку, если это концепция, делаем отметку в концепции
            if ($is_concept && $this->getActivity($request)->getAllowSpecialAgreement()) {
                $model->setSpecialAgreement(true);
                $model->save();
            }

            //Учитываем изменение параметров заявки
            if ($model->getModelCategoryId() != $request->getParameter('model_category_id') && !$is_concept) {
                $entry = new LogEntry();
                $entry->setArray(array(
                    'user_id' => $this->getUser()->getAuthUser()->getId(),
                    'login' => $this->getUser()->getAuthUser()->getEmail(),
                    'title' => 'Обновление параметров заявки.',
                    'description' => 'Обновление параметров заявки. <br/>' . $object_params_str,
                    'icon' => '',
                    'object_id' => $model->getId(),
                    'object_type' => 'agreement_model',
                    'action' => 'agreement_model_update_params',
                    'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                    'message_id' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'private_user_id' => 0
                ));
                $entry->save();
            }

            if (!$model->isModelScenario()) {
                $saved_files = UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_ids, $model->getActivityId())->saveFiles();
            }
            else if ($no_model_changes && $model->isModelScenario()) {
                $saved_scenario_files = UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_ids, $model->getActivityId(),'Scenario')->saveFiles();
                $saved_record_files = UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_records_ids, $model->getActivityId(),'Record')->saveFiles();

                $saved_files = array_merge($saved_scenario_files, $saved_record_files);
            } else if ($model->isModelScenario()) {
                $saved_files = UploadModelFilesFactory::getInstance()->createUpload($model, $this->getUser(), $upload_files_ids, $model->getActivityId(),'ScenarioRecord')->saveFiles();
            }

            //$saved_files = $this->saveModelFiles($model, $upload_files_ids, $model_type_id);

            /*$modelType = AgreementModelTypeTable::getInstance()->find($model_type_id);
            $model->setModelType($modelType);*/

            $model->setAgreementComments('');

            $model->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $model->setFilesIds(strlen($upload_files_ids) > 0 ? $upload_files_ids :
                    implode(':', array_map(function($item) {
                        return $item['gen_file_name'];
                    }, $saved_files
                    ))
            );
            $model->save();

            $this->updateModelValuesByType($model, $request);

            /**
             * Set model discussion messages statuses to none
             * Must do this when update out model status
             */
            $model->nullDiscussionMessagesStatuses();

            if ($model->isModelScenario()) {
                if ($model->getStep1() == "none") {
                    $model->setStep1("wait");
                } else if ($model->getStep1() == "accepted") {
                    $model->setStep2('wait');
                }

                $model->save();
            }

            if ($this->getActivity($request)->getAllowCertificate() && !$model->isConcept()) {
                $model->setConceptId($request->getParameter('concept_id'));
                $model->save();
            }

            if ($model->isConcept() && ($this->getActivity($request)->getAllowCertificate() || $this->getActivity($request)->getAllowSpecialAgreement())) {
                $this->addDatesPeriodAction($request, $model);
            }

            /**
             * Save files only when No model change is checked and model type is scenario / record
             */
            /*if ($no_model_changes && $model->isModelScenario()) {
                $record_saved_files = $this->saveModelFiles($model, $upload_files_records_ids);

                $saved_files = array_merge($saved_files, $record_saved_files);
            }*/

            if (!$draft) {
                $accept_utils = new AgreementModelAcceptByDealer(array(
                    'model' => $model,
                    'user' => $this->getUser()->getAuthUser(),
                    'saved_files' => $saved_files,
                ));
                $message = $accept_utils->agreementUpdate();

                //$this->setModelChanges($model, $no_model_changes);
            }
        }

        $message_data = null;
        /*if ($message) {
            $message_data = Utils::formatMessageData($message);
        }*/

        return $this->sendFormBindResult($form,
            $model->isValidModelCategory() ? 'agreement_model_with_category_form.onResponse' : 'agreement_model_form.onResponse',
            '',
            $message_data);
    }

    /**
     * @param sfWebRequest $request
     * @param AgreementModel $model
     */
    function addDatesPeriodAction(sfWebRequest $request, AgreementModel $model)
    {
        //Дата окончания действия сертификата для концепции
        $modelSett = AgreementModelSettingsTable::getInstance()->createQuery()->where('model_id = ?', $model->getId())->fetchOne();
        if (!$modelSett)
            $modelSett = new AgreementModelSettings();

        $modelSett->setArray(array('model_id' => $model->getId(),
            'certificate_date_to' => date('Y-m-d', strtotime(str_replace('.', '-', $request->getParameter('date_of_certificate_end'))))));
        $modelSett->save();

        //Переиоды проведения мероприятий
        AgreementModelDatesTable::getInstance()->createQuery()->where('model_id = ?', $model->getId())->delete()->execute();

        $datesIndex = 0;
        $datesFieldsStart = $request->getParameter('dates_of_service_action_start');
        $datesFieldsEnd = $request->getParameter('dates_of_service_action_end');

        foreach ($datesFieldsStart as $dateField) {
            $date1 = date('Y-m-d', strtotime(str_replace('.', '-', $dateField)));
            $date2 = date('Y-m-d', strtotime(str_replace('.', '-', $datesFieldsEnd[$datesIndex++])));

            $joinDate = sprintf('%s/%s', $date1, $date2);

            $dateModel = new AgreementModelDates();
            $dateModel->setArray(array('model_id' => $model->getId(),
                'activity_id' => $this->getActivity($request)->getId(),
                'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                'date_of' => $joinDate));
            $dateModel->save();
        }

    }


    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeEdit(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Asset', 'Tag'));

        $model = $this->getModel($request);

        $this->getUser()->setAttribute('editor_link', '');

        $model_type_identifier= $model->getModelType()->getIdentifier();
        if ($model) {
            $reportId = $model->getReportId();
            $model_period = $model->getPeriod();
            $result = array(
                'success' => true,
                'values' => array(
                    'id' => $model->getId(),
                    'activity_id' => $model->getActivityId(),
                    'activity' => $model->getActivity()->getName(),
                    'name' => $model->getName(),
                    'blank_id' => $model->getBlankId(),
                    'model_type_id' => $model->getModelTypeId(),
                    'model_category_id' => $model->getModelCategoryId(),
                    'is_valid_model_category' => $model->isValidModelCategory(),
                    'period' => !empty($model_period) ? $model_period : '',
                    'is_model_scenario' => $model->isModelScenario(),
                    'task_id' => $model->getTaskId(),
                    'target' => $model->getTarget(),
                    'cost' => $model->getCost(),
                    'status' => $model->getStatus(),
                    'css_status' => $model->getCssStatus(),
                    'model_blocked' => $model->getIsBlocked() && !$model->getAllowUseBlocked(),
                    'allowUseBlocked' => $model->getAllowUseBlocked(),
                    'accept_in_model' => $model->getAcceptInModel(),
                    'haveReport' => empty($reportId) ? 0 : 1,
                    'reportStatus' => !empty($reportId) ? $model->getReport()->getStatus() : '',
                    'no_model_changes' => $model->getNoModelChanges(),
                    'model_accepted_in_online_redactor' => $model->getModelAcceptedInOnlineRedactor(),
                    'model_file' => $model->getModelFile() ? array(
                        'path' => url_for('@agreement_model_download_file?file=' . $model->getModelFile()),
                        'name' => $model->getModelFile(),
                        'size' => $model->getModelFileNameHelper()->getSmartSize()
                    ) : '',
                    'step1' => $model->getStep1() == 'accepted' ? true : false,
                    'step2' => $model->getStep2() == 'accepted' ? true : false,
                    'step1_value' => $model->getStep1(),
                    'step2_value' => $model->getStep2(),
                    'designer_status' => $model->getDesignerStatus(),
                    'editor_link' => $model->getEditorLink(),
                    'concept_id' => $model->getConceptId(),
                    'share_name' => $model->getShareName(),
                    'model_type_data' => $this->makeModelTypeLabel($model),
                    'model_type_identifier' => $model_type_identifier,
                    'is_draft' => $model->getStatus() == 'not_sent' ? true : false
                )
            );

            if ($model->isModelScenario()) {
                $result['values']['model_uploaded_scenario_files'] = $model->makeListOfUploadedFilesByType(AgreementModel::BY_SCENARIO);
                $result['values']['model_uploaded_record_files'] = $model->makeListOfUploadedFilesByType(AgreementModel::BY_RECORD);
            } else {
                $result['values']['model_uploaded_files'] = $model->makeListOfUploadedFilesByType(AgreementModel::UPLOADED_FILE_MODEL);
            }

            if ($model->getEditorLink()) {
                $result['values']['model_file'] = $model->getModelFile() ? array(
                    'path' => $model->getModelFile(),
                    'name' => $model->getModelFile(),
                    'size' => Utils::getRemoteFileSize($model->getModelFile())
                ) : '';
            } else {
                $result['values']['model_file'] = $model->getModelFile() ? array(
                    'path' => url_for('@agreement_model_download_file?file=' . $model->getModelFile()),
                    'name' => $model->getModelFile(),
                    'size' => $model->getModelFileNameHelper()->getSmartSize()
                ) : '';
            }

            $prefix = ($model->getModelCategoryId() != 0 && !$model->getModelCategory()->getIsBlank()) ? $model->getModelCategory()->getIdentifier() : $model->getModelType()->getIdentifier();

            foreach ($model->getValuesByType() as $name => $value) {
                $result['values'][$prefix . '[' . $name . ']'] = $value;
            }
        } else {
            $result = array(
                'success' => false,
                'error' => 'not_found'
            );
        }

        return $this->sendJson($result);
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeDelete(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if ($model && ($model->getStatus() == 'not_sent' || $model->getStatus() == 'declined')) {
            $text = $model->isConcept() ? 'Концепция удалена' : 'Макет удалён';
            $entry = LogEntryTable::getInstance()->addEntry(
                $this->getUser()->getAuthUser(),
                $model->isConcept() ? 'agreement_concept' : 'agreement_model',
                'delete',
                $model->getActivity()->getName() . '/' . $model->getName(),
                $text,
                '',
                $model->getDealer(),
                $model->getId(),
                'agreement'
            );

            $model->createPrivateLogEntryForSpecialists($entry);
            AgreementModelReportFilesTable::unlinkModelsFiles($model->getId());

            $model->delete();

            $utils = new AgreementActivityStatusUtils($model->getActivity(), $model->getDealer());
            $utils->updateActivityAcceptance();
            $utils->updateActivityModelsNecessarilyTypes($this->getUser()->getAuthUser(), $model->getActivity()->getId(), $model->getIsNecessarilyModel());
        }

        return sfView::NONE;
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeCancel(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if ($model && $model->getStatus() != 'accepted') {
            $accept_utils = new AgreementModelAcceptByDealer(array(
                'model' => $model,
                'user' => $this->getUser()->getAuthUser()
            ));

            $message = $accept_utils->cancel();
        }

        $result = array('success' => true);

        $message_data = null;
        /*if ($message) {
            $result['message_data'] = Utils::formatMessageData($message);
        }*/

        return $this->sendJson($result);
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeCancelScenario(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if ($model && $model->getStatus() != 'accepted') {
            $accept_utils = new AgreementModelAcceptByDealer(array(
                'model' => $model,
                'user' => $this->getUser()->getAuthUser()
            ));

            $message = $accept_utils->cancelScenario();
        }

        $result = array('success' => true);

        $message_data = null;
        /*if ($message) {
            $result['message_data'] = Utils::formatMessageData($message);
        }*/

        return $this->sendJson($result);
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeCancelRecord(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if ($model && $model->getStatus() != 'accepted') {
            $accept_utils = new AgreementModelAcceptByDealer(array(
                'model' => $model,
                'user' => $this->getUser()->getAuthUser()
            ));

            $message = $accept_utils->cancelRecord();
        }

        $result = array('success' => true);

        $message_data = null;
        /*if ($message) {
            $result['message_data'] = Utils::formatMessageData($message);
        }*/

        return $this->sendJson($result);
    }

    /**
     *
     */
    function outputModelTypes()
    {
        $this->model_types = AgreementModelTypeTable::getInstance()
            ->createQuery()
            ->where('concept=?', 0)
            ->andWhere('parent_category_id = ?', 0)
            ->execute();
    }

    /**
     * @param $request
     * @return null
     */
    function outputTaskList($request)
    {
        $activity = ActivityTable::getInstance()->find($request->getParameter('activity'));

        if (!empty($activity))
            return $activity->getTasks();
        //$this->task_lists =

        return null;
    }

    /**
     *
     */
    function outputModelTypesFields()
    {
        $fields = array();
        $db_fields = AgreementModelFieldTable::getInstance()
            ->createQuery('f')
            ->innerJoin('f.ModelType t')
            ->where('f.parent_category_id = ?', 0)
            ->execute();

        foreach ($db_fields as $field) {
            if (!isset($fields[$field->getModelTypeId()]))
                $fields[$field->getModelTypeId()] = array();

            $fields[$field->getModelTypeId()][] = AgreementModelFieldRendererFactory::getInstance()->create($field);
        }

        $this->model_types_fields = $fields;
    }

    /**
     * @param sfWebRequest $request
     */
    function outputModels(sfWebRequest $request)
    {
        $sorts = array(
            'id' => 'm.id',
            'name' => 'm.name',
            'cost' => 'm.cost'
        );

        $sort_column = $this->getSortColumn();
        $sort_direct = $this->getSortDirection();

        $sql_sort = 'm.id';
        if (isset($sorts[$sort_column])) {
            $sql_sort = $sorts[$sort_column] . ' ' . ($sort_direct ? 'DESC' : 'ASC');
        }

        $activity = $this->getActivity($request);
        $models_result = $activity->getModelsList($this->getUser(), $sql_sort, $this->current_q, false, $this->current_year);

        $this->models = array();
        if (isset($models_result['models'])) {
            $this->models = $models_result['models'];
        }

        $this->necessarily_models = array();
        if (isset($models_result['necessarily_models'])) {
            $this->necessarily_models = $models_result['necessarily_models'];
        }
    }

    /**
     * @param sfWebRequest $request
     */
    function outputBlanks(sfWebRequest $request)
    {
        $this->blanks = AgreementModelBlankTable::getInstance()
            ->createQuery('b')
            ->select('b.*, mt.*')
            ->leftJoin('b.Models m WITH m.dealer_id=?', $this->getUser()->getAuthUser()->getDealer()->getId())
            ->innerJoin('b.ModelType mt')
            ->where('b.activity_id=? and m.id is null', $this->getActivity($request)->getId())
            ->execute();
    }

    /**
     *
     */
    function outputDealerFiles()
    {
        $this->dealer_files = $this->getUser()->getAuthUser()->getDealerFiles();
    }

    /**
     * @param sfWebRequest $request
     */
    function outputHasConcept(sfWebRequest $request)
    {
        $activity = $this->getActivity($request);

        $this->has_concept = $activity->getHasConcept() || $activity->getAllowSpecialAgreement();
    }

    /**
     *
     */
    function outputModelFilter()
    {
        $this->model_filter = $this->getModelFilter();
    }

    /**
     * @param sfWebRequest $request
     */
    function outputConcept(sfWebRequest $request)
    {
        $sorts = array(
            'id' => 'm.id',
            'name' => 'm.name',
            'cost' => 'm.cost'
        );


        $sort_column = $this->getSortColumn();
        $sort_direct = $this->getSortDirection();

        $sql_sort = 'm.id';
        if (isset($sorts[$sort_column])) {
            $sql_sort = $sorts[$sort_column] . ' ' . ($sort_direct ? 'DESC' : 'ASC');
        }

        $activity = $this->getActivity($request);
        $models_result = $activity->getModelsList($this->getUser(), $sql_sort, $this->current_q, true, $this->current_year);

        $this->concept = array();
        if (isset($models_result['models'])) {
            $this->concept = $models_result['models'];
        }

        /*$this->concept = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->select('m.*')
            ->innerJoin('m.ModelType mt WITH mt.concept=?', true)
            ->where('m.dealer_id=?', $this->getUser()->getAuthUser()->getDealer()->getId())
            ->andWhere('m.activity_id=?', $this->getActivity($this->getRequest())->getId())
            //->fetchOne();
                ->orderBy('id ASC')
            ->execute();*/
    }

    /**
     *
     */
    function outputConceptType()
    {
        $this->concept_type = AgreementModelTypeTable::getInstance()
            ->createQuery()
            ->where('concept<>?', 0)
            ->fetchOne();
    }

    /**
     * @param AgreementModel $model
     * @param sfWebRequest $request
     */
    function updateModelValuesByType(AgreementModel $model, sfWebRequest $request)
    {
        if ($model->getModelCategoryId() && !$model->getModelCategory()->getIsBlank()) {
            $model->setValuesByType($this->cleanValues($request->getParameter($model->getModelCategory()->getIdentifier())));
        } else {
            $model->setValuesByType($this->cleanValues($request->getParameter($model->getModelType()->getIdentifier())));
        }
    }

    /**
     * @param $values
     * @return array
     */
    protected function cleanValues($values)
    {
        if (empty($values) || is_null($values)) {
            return array();
        }

        foreach ($values as &$value) {
            $value = trim(strip_tags($value));
        }

        return $values;
    }

    /**
     * @param sfWebRequest $request
     * @return array
     */
    function getModelFiles(sfWebRequest $request)
    {
        $files = $request->getFiles();
        if (!is_array($files))
            return $files;

        if (isset($files['model_file']) && isset($files['model_file']['tmp_name']) && $files['model_file']['tmp_name'])
            return $files;

        $server_file = $request->getPostParameter('server_model_file');
        if (!$server_file || preg_match('#[\\\/]#', $server_file))
            return $files;

        $tmp_name = $this->getUser()->getAuthUser()->getDealerUploadPath() . '/' . $server_file;
        if (!file_exists($tmp_name))
            return $files;

        $files['model_file'] = array(
            'name' => $server_file,
            'tmp_name' => $tmp_name,
            'type' => F::getFileMimeType($server_file)
        );

        return $files;
    }

    /**
     * @param sfWebRequest $request
     * @return array
     */
    function getModelRecordFiles(sfWebRequest $request)
    {
        $files = $request->getFiles();
        if (!is_array($files))
            return $files;

        if (isset($files['model_record_file']) && isset($files['model_record_file']['tmp_name']) && $files['model_record_file']['tmp_name'])
            return $files;

        $server_file = $request->getPostParameter('server_model_record_file');
        if (!$server_file || preg_match('#[\\\/]#', $server_file))
            return $files;

        $tmp_name = $this->getUser()->getAuthUser()->getDealerUploadPath() . '/' . $server_file;
        if (!file_exists($tmp_name))
            return $files;

        $files['model_record_file'] = array(
            'name' => $server_file,
            'tmp_name' => $tmp_name,
            'type' => F::getFileMimeType($server_file)
        );

        return $files;
    }

    /**
     * Returns an agreement model
     *
     * @param sfWebRequest $request
     * @return AgreementModel|false
     */
    protected function getModel(sfWebRequest $request)
    {
        $activity = $this->getActivity($request);
        $dealer = $this->getUser()->getAuthUser()->getDealer();

        $model = AgreementModelTable::getInstance()
            ->createQuery()
            ->where('activity_id=? and dealer_id=? and id=?', array($activity->getId(), $dealer->getId(), $request->getParameter('id')))
            ->fetchOne();

        $this->reorderObjectFilesByType($model, 'ModelFile', 1);
        $this->reorderObjectFilesByType($model, 'ModelRecordFile', 1);

        $report = $model->getReport();
        $reportId = $report->getId();
        if ($report && !empty($reportId)) {

            $this->reorderObjectFilesByType($report, 'AdditionalFile', 2, 7);
            $this->reorderObjectFilesByType($report, 'FinancialDocsFile', 1);
            $this->reorderObjectFilesByType($report, 'AdditionalFileExt', 1);
        }

        return $model;
    }

    /**
     * @param $obj
     * @param $field
     * @param $from
     * @param int $maxFiles
     */
    private function reorderObjectFilesByType($obj, $field, $from, $maxFiles = self::MAX_FILES)
    {
        $files = array();
        for ($ind = $from; $ind <= $maxFiles; $ind++) {
            $funcGet = self::GETTER . $field . $ind;
            $funcSet = self::SETTER . $field . $ind;

            $file = $obj->$funcGet();
            if (!empty($file)) {
                $files[] = $file;

                $obj->$funcSet('');
            }
        }

        for ($ind = 0; $ind < count($files); $ind++) {
            $func = self::SETTER . $field . $from++;

            $file = $files[$ind];
            $obj->$func($file);
        }

        $obj->save();
    }

    /**
     * @param AgreementModel $model
     * @param Message $message
     * @param bool $editor
     */
    protected function attachFileToMessage(AgreementModel $model, Message $message, $editor = false)
    {
        $file = new MessageFile();
        $file->setMessageId($message->getId());

        if (!$editor) {
            $file->setFile($message->getId() . '-' . $model->getModelFile());
            copy(
                sfConfig::get('sf_upload_dir') . '/' . AgreementModel::MODEL_FILE_PATH . '/' . $model->getModelFile(),
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
            );
        } else {
            $file->setFile($model->getModelFile());
            $file->setEditor(true);
        }

        $file->save();
    }

    /**
     * Save model uploaded files to discussion by model type (simple model or scenario / record)
     * @param AgreementModel $model
     * @param Message $message
     * @param array $saved_files
     */
    protected function attachFilesToMessage(AgreementModel $model, Message $message, $saved_files = array())
    {
        if (!empty($saved_files)) {
            foreach ($saved_files as $file_item) {
                $path = $file_item['gen_file_name'];

                if (isset($file_item['upload_path']) && !empty($file_item['upload_path'])) {
                    $path = sprintf('%s/%s', $file_item['upload_path'], $file_item['gen_file_name']);
                }

                $this->saveMessageFile($message, $path);
            }
        } else {
            $query = AgreementModelReportFilesTable::getInstance()->createQuery()->select('file, path')
                ->where('object_id = ?', $model->getId())
                ->orderBy('id ASC');

            if ($model->isModelScenario() && $model->getStep1() != 'accepted') {
                $query->andWhere('object_type = ? and (file_type = ? or file_type = ?)', array(AgreementModel::UPLOADED_FILE_MODEL, AgreementModel::UPLOADED_FILE_MODEL_TYPE, AgreementModel::UPLOADED_FILE_SCENARIO_TYPE));
            }

            $files_list = $query->execute();
            foreach ($files_list as $file_item) {
                $this->saveMessageFile($message, $file_item->getFileName());
            }
        }
    }

    /**
     * @param $message
     * @param $file_to_save
     */
    private function saveMessageFile($message, $file_to_save)
    {
        if ($file_to_save && file_exists(sfConfig::get('sf_upload_dir') . '/' . AgreementModel::MODEL_FILE_PATH . '/' . $file_to_save)) {
            $file = new MessageFile();

            $file->setMessageId($message->getId());
            $file->setFile($message->getId() . '-' . $file_to_save);

            copy(
                sfConfig::get('sf_upload_dir') . '/' . AgreementModel::MODEL_FILE_PATH . '/' . $file_to_save,
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
            );

            $file->save();
        }
    }

    /**
     * Add message to discussion
     *
     * @param AgreementModel $model
     * @param string $text
     * @return Message|false
     */
    protected function addMessageToDiscussion(AgreementModel $model, $text, $msg_show = true, $msg_status = 'none')
    {
        $discussion = $model->getDiscussion();

        if (!$discussion)
            return;

        $message = new Message();
        $user = $this->getUser()->getAuthUser();
        $message->setDiscussionId($discussion->getId());
        $message->setUser($user);
        $message->setUserName($user->selectName());
        $message->setText($text);
        $message->setSystem(true);
        $message->setMsgShow($msg_show);
        $message->setMsgStatus($msg_status);
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    /**
     * @return mixed
     */
    function getSortColumn()
    {
        return $this->getUser()->getAttribute(self::SORT_ATTR, 'id');
    }

    /**
     * @return mixed
     */
    function getSortDirection()
    {
        return $this->getUser()->getAttribute(self::SORT_DIRECT_ATTR, false);
    }

    /**
     * @param $column
     */
    function setSortColumn($column)
    {
        $this->getUser()->setAttribute(self::SORT_ATTR, $column);
    }

    /**
     * @param $direction
     */
    function setSortDirection($direction)
    {
        $this->getUser()->setAttribute(self::SORT_DIRECT_ATTR, $direction);
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeChangeModelPeriod(sfWebRequest $request)
    {
        $modelId = $request->getParameter('modelId');
        $fieldId = $request->getParameter('fieldId');
        $period = $request->getParameter('period');
        $period_type = $request->getParameter('period_type');

        if ($period_type == 'by_model_type') {
            $modelValue = AgreementModelValueTable::getInstance()->createQuery()->where('model_id = ? and field_id = ?', array($modelId, $fieldId))->fetchOne();
            if ($modelValue) {
                $modelValue->setValue($period);
                $modelValue->save();

                AgreementModelsBlockInformTable::getInstance()->createQuery()->where('model_id = ?', $modelId)->delete();
            }
        } else {
            $model = AgreementModelTable::getInstance()->find($modelId);

            //Parse period date, get end of period
            $new_period = D::formatModelPeriod($period);
            $model_period = $model->getPeriod();

            $old_period = D::formatModelPeriod($model_period);

            //If new period is greater than old period we add to block info new record
            //But before we calc how many days was added when changed period
            //if (strtotime($new_period) > strtotime($old_period)) {
                /*$differ_date = strtotime($new_period) - strtotime($old_period);
                $days = $days = floor($differ_date / 3600 / 24);

                //Check if we have blocked information in db, get last record and get date
                if (AgreementModelsBlockInformTable::getInstance()->createQuery()->where('model_id = ?', $modelId)->count() > 0) {
                    $block_info_data = AgreementModelsBlockInformTable::getInstance()->createQuery()->where('model_id = ?', $modelId)->orderBy('id DESC')->fetchOne();

                    if ($block_info_data) {
                        //Add new record to block info table, with new date and block type, relying on count of added days by new period
                        $new_block_date = date('Y-m-d H:i:s', strtotime('+'.$days.' days', strtotime($block_info_data->getCreatedAt())));

                        $block_info_data->setBlockType($days > 2 ? DealersModelsInformLeftDays::MODEL_10_DAYS_LEFT_LABEL : DealersModelsInformLeftDays::MODEL_2_DAYS_LEFT_LABEL );
                        $block_info_data->setCreatedAt($new_block_date);
                        $block_info_data->save();
                    }
                }*/
            //}

            if ($model) {
                //Удаляем данные по блокировке заявки
                if (AgreementModelsBlockInformTable::getInstance()->createQuery()->where('model_id = ?', $modelId)->count()) {
                    AgreementModelsBlockInformTable::getInstance()->createQuery()->where('model_id = ?', $modelId)->delete()->execute();
                }

                $model->setPeriod($period);
                $model->save();
            }
        }

        return sfView::NONE;
    }

    /**
     * @param $model
     * @param $modelTypeId
     * @param $noModelChanges
     */
    private function setModelChanges($model, $modelTypeId, $noModelChanges)
    {
        if ($noModelChanges && $model->getStep1() != "accepted" && ($modelTypeId == 2 || $modelTypeId == 4)) {
            $model->setStep1('accepted');
            $model->setStep2('accepted');

            $model->setStatus('accepted');

            $model->save();
        } else if ($noModelChanges && $model->getStatus() != "accepted") {
            $model->setStatus('accepted');
            $model->save();
        }
    }

    /**
     * @param sfWebRequest $request
     */
    function executeModelRecordBlock(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        if ($id != 0) {
            $this->childs = $request->getParameter('childs');
            $this->model = AgreementModelTable::getInstance()->find($id);
        }

    }

    /**
     * @param sfWebRequest $request
     */
    function executeModelFilesBlock(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        if ($id != 0) {
            $this->childs = $request->getParameter('childs');
            $this->model = AgreementModelTable::getInstance()->find($id);
        }
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    function executeAddExternal(sfWebRequest $request)
    {
        $activityId = $request->getParameter('activity');

        $this->link = str_replace('-', '/', $request->getParameter('link'));
        $this->link = base64_decode($this->link);

        $this->hash = $request->getParameter('hash');

        if ($this->hash != md5($activityId . $this->link . self::REDACTOR_KEY)) {
            return sfView::ERROR;
        }

        $this->getUser()->setAttribute('editor_link', $this->link);

        $this->executeIndex($request);
        $this->setTemplate('index');
    }

    /**
     * @param sfWebRequest $request
     */
    function executeAddManyConcepts(sfWebRequest $request)
    {

    }

    /**
     * @param sfWebRequest $request
     */
    public function executeModelDatesField(sfWebRequest $request)
    {
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeLoadModelDatesAndCertificates(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $this->model = AgreementModelTable::getInstance()->find($id);
        if ($this->model->getActivity()->getAllowCertificate() || $this->model->getActivity()->getAllowSpecialAgreement()) {
            $this->dates = AgreementModelDatesTable::getInstance()->createQuery()->select('date_of')->where('model_id = ?', $id)->orderBy('id ASC')->execute();

            $settModel = AgreementModelSettingsTable::getInstance()->createQuery()->where('model_id = ?', $id)->fetchOne();
            if ($settModel)
                $this->certificateDate = $settModel->getCertificateDateTo();
        } else
            return sfView::NONE;
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeDatesDelete(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $date = AgreementModelDatesTable::getInstance()->find($id);
        if ($date) {
            $date->delete();

            return $this->sendJson(array('success' => true));
        }

        return $this->sendJson(array('success' => false));
    }

    /**
     * @param sfWebRequest $request
     */
    public function executeDownloadFile(sfWebRequest $request)
    {
        $file_id = $request->getParameter('file');

        $file_item = AgreementModelReportFilesTable::getInstance()->find($file_id);
        if ($file_item) {
            $path = AgreementModel::MODEL_FILE_PATH;
            if ($file_item->getFileType() == AgreementModelReport::UPLOADED_FILE_ADDITIONAL) {
                $path = AgreementModelReport::ADDITIONAL_FILE_PATH;
            } else if ($file_item->getFileType() == AgreementModelReport::UPLOADED_FILE_FINANCIAL) {
                $path = AgreementModelReport::FINANCIAL_DOCS_FILE_PATH;
            }

            $filePath = sfConfig::get('app_uploads_path') . '/' . $path . '/' . $file_item->getFileName();
            if (file_exists($filePath)) {
                $file = end(explode('/', $filePath));

                if (!F::downloadFile($filePath, $file)) {
                    $this->getResponse()->setContentType('application/json');
                    $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Файл не найден')));
                }
            }
        }
    }

    /**
     * @param sfWebRequest $request
     */
    public function executeLoadConceptCertFields(sfWebRequest $request)
    {
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeCopyModel(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

        $model_id = $request->getParameter('model_id');

        $model = AgreementModelTable::getInstance()->createQuery()->select('activity_id, dealer_id, name, model_category_id, model_type_id, target, cost, task_id')->where('id = ?', $model_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
        if ($model) {
            $model_id = $model['id'];
            $model_fields_values = AgreementModelValueTable::getInstance()
                ->createQuery('amv')
                ->leftJoin('amv.Field amf')
                ->where('amv.model_id = ?', $model['id'])
                ->andWhere('amf.type != ?', 'period')
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            unset($model['id']);
            unset($model['report_id']);
            unset($model['discussion_id']);

            $model['status'] = 'not_sent';

            $copy_model = new AgreementModel();
            $copy_model->setArray($model);
            $copy_model->save();

            foreach ($model_fields_values as $model_value) {
                unset($model_value['id']);
                unset($model_value['Field']);

                $model_value['model_id'] = $copy_model->getId();

                $copy_model_value = new AgreementModelValue();
                $copy_model_value->setArray($model_value);
                $copy_model_value->save();
            }

            LogEntryTable::getInstance()->addEntry(
                $this->getUser()->getAuthUser(),
                $copy_model->isConcept() ? 'agreement_concept_copy' : 'agreement_model_copy',
                'model_copy',
                $copy_model->getActivity()->getName() . '/' . $copy_model->getName(),
                sprintf('Создана копия заявки: %s из %s', $copy_model->getId(), $model_id),
                'copy',
                $copy_model->getDealer(),
                $copy_model->getId(),
                'agreement'
            );

            return $this->sendJson
            (
                array
                (
                    'success' => true,
                    'url' => url_for('@agreement_module_models_model_copy?activity='.$model['activity_id'].'&model='.$copy_model->getId().'&current_q='.D::getQuarter($copy_model->getCreatedAt())
                    )
                )
            );
        }

        return $this->sendJson(array('success' => false));
    }

    /**
     * @param $model
     * @param $upload_files_ids
     * @return array
     * @internal param $form
     */
    private function saveModelFiles($model, $upload_files_ids, $no_model_changes = false)
    {
        $fileModel = AgreementModel::UPLOADED_FILE_MODEL;
        $fileModelType = AgreementModel::UPLOADED_FILE_MODEL_TYPE;

        /**
         * Make check what model type and then get all uploaded files by model type
         */
        if ($this->getUser()->getAttribute('editor_link')) {
            $editor_file_name = F::copyExternalFileTo($this->getUser()->getAttribute('editor_link'), sfConfig::get('sf_upload_dir') . '/' . TempFile::FILE_PATH);
            $temp_file = new TempFile();
            $temp_file->setArray(
                array
                (
                    'file' => $editor_file_name,
                    'file_object_type' => AgreementModel::UPLOADED_FILE_MODEL,
                    'file_type' => AgreementModel::UPLOADED_FILE_MODEL_TYPE,
                    'user_id' => $this->getUser()->getAuthUser()->getId(),
                    'is_external_file' => true
                )
            );
            $temp_file->save();

            $upload_files_ids[] = $temp_file->getId();
        } else {
            if ($model->isModelScenario()) {
                if ($model->getStep1() == "accepted") {
                    $fileModel = AgreementModel::UPLOADED_FILE_RECORD;
                    $fileModelType = AgreementModel::UPLOADED_FILE_RECORD_TYPE;

                    $model_uploaded_files = $model->getModelUploadedScenarioRecordFiles(AgreementModel::BY_RECORD);
                } else {
                    $fileModel = AgreementModel::UPLOADED_FILE_SCENARIO;
                    $fileModelType = AgreementModel::UPLOADED_FILE_SCENARIO_TYPE;

                    $model_uploaded_files = $model->getModelUploadedScenarioRecordFiles(AgreementModel::BY_SCENARIO);
                    if ($no_model_changes) {
                        $model_uploaded_record_files = $model->getModelUploadedScenarioRecordFiles(AgreementModel::BY_RECORD);
                    }
                }
            } else {
                $model_uploaded_files = $model->getModelUploadedFiles();
            }
        }

        /**
         * If model type was changed then change uploaded model files to this type
         */
        foreach ($model_uploaded_files as $file) {
            $file->setFileType($fileModelType);
            $file->save();
        }

        if (!is_array($upload_files_ids)) {
            $upload_files_ids = explode(":", $upload_files_ids);
        }

        $temp_files_list = TempFileTable::getInstance()->createQuery()
            ->whereIn('id', $upload_files_ids)
            ->andWhere('file_object_type = ? and file_type = ?', array($fileModel, $fileModelType))
            ->execute();

        $copied_files = array();
        foreach ($temp_files_list as $temp_file) {
            $copy_file = TempFileTable::copyFileTo($temp_file, AgreementModel::MODEL_FILE_PATH);

            $copied_files[] = $copy_file;
            $record = new AgreementModelReportFiles();
            $record->setArray(
                array(
                    'file' => $copy_file,
                    'object_id' => $model->getId(),
                    'object_type' => $fileModel,
                    'file_type' => $fileModelType,
                    'user_id' => $this->getUser()->getAuthUser()->getId(),
                    'field' => '',
                    'field_name' => '',
                    'is_external_file' => $temp_file->getIsExternalFile()
                )
            );

            $record->save();

            TempFileTable::removeFile($temp_file);
        }

        if (!empty($model_uploaded_files)) {
            foreach ($model_uploaded_files as $model_file) {
                $copied_files[] = $model_file->getFile();
            }
        }

        return $copied_files;
    }

    /**
     * @param sfWebRequest $request
     */
    public function executeDeleteModelUploadedFile(sfWebRequest $request)
    {
        $file_id = $request->getParameter('id');

        $file_item = AgreementModelReportFilesTable::getInstance()->find($file_id);
        if ($file_item) {
            $description = '';

            if ($file_item->getObjectType() == AgreementModel::UPLOADED_FILE_MODEL) {
                $this->model = AgreementModelTable::getInstance()
                    ->createQuery()
                    ->where('id = ?', $file_item->getObjectId())
                    ->fetchOne();

                $description = sprintf('Файл %s был удален из заявки №%s', $file_item->getFile(), $file_item->getObjectId());
            } else if($file_item->getObjectType() == AgreementModelReport::UPLOADED_FILE_REPORT) {
                $description = sprintf('Файл %s был удален из отчета №%s', $file_item->getFile(), $file_item->getObjectId());
            }

            $log_item = new LogEntry();
            $log_item->setArray(array(
                'user_id' => $this->getUser()->getAuthUser()->getId(),
                'description' => $description,
                'object_id' => $file_item->getObjectId(),
                'action' => 'uploaded_file_delete',
                'object_type' => 'agreement_model_report',
                'login' => $this->getUser()->getAuthUser()->getEmail(),
                'title' => 'Удаление файла',
                'module_id' => 1
            ));
            $log_item->save();
        }

        $this->setTemplate('modelFilesBlock');
        if ($file_item) {
            if ($file_item->getFileType() == AgreementModel::UPLOADED_FILE_RECORD_TYPE) {
                $this->setTemplate('modelRecordBlock');
            }

            $file_item->delete();
        }
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeDeleteModelUploadedFiles(sfWebRequest $request) {
        $files_ids = $request->getParameter('files_ids');
        $file_id = array_pop($files_ids);

        $total_files_deleted = 0;
        if (!empty($file_id)) {
            $file_item = AgreementModelReportFilesTable::getInstance()->find($file_id);
            if ($file_item) {
                $object_id = $file_item->getObjectId();

                /*$model_obj = AgreementModelTable::getInstance()->find($object_id);
                if ($model_obj) {
                    $model_obj->setStep1("none");
                    $model_obj->setStep2("none");
                    $model_obj->save();
                }*/

                $files_list = AgreementModelReportFilesTable::getInstance()->createQuery()->where('object_id = ?', $object_id)->execute();
                foreach ($files_list as $file) {
                    $file->delete();
                    $total_files_deleted++;
                }
            }
        }

        return $this->sendJson(array('success' => $total_files_deleted > 0 ? true : false));
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeModelTypeIdentity(sfWebRequest $request) {
        $identity_from_category = (int)$request->getParameter('identity_from_category') == 1 ? true : false;
        $model_type = AgreementModelTypeTable::getInstance()->find($request->getParameter('id'));

        if ($identity_from_category && $model_type) {
            return $this->sendJson(array('success' => true, 'model_type_identity' => $model_type->getIdentifier()));
        } else {
            if ($model_type) {
                return $this->sendJson(array('success' => true, 'model_type_identity' => $model_type->getIdentifier()));
            }
        }

        return $this->sendJson(array('success' => false));
    }

    /**
     * @param sfWebRequest $request
     */
    public function executeGetCategoryTypes(sfWebRequest $request)
    {
        $this->model_type = null;
        $this->model_types = AgreementModelTypeTable::getInstance()->createQuery()->where('parent_category_id = ?', $request->getParameter('category_id'))->orderBy('position ASC')->execute();

        $model_id = $request->getParameter('model_id');
        if ($model_id != 0) {
            $this->model = AgreementModelTable::getInstance()->find($model_id);

            if ($this->model) {
                $this->model_type = AgreementModelTypeTable::getInstance()->find($this->model->getModelTypeId());
            }
        }
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeGetModelTypeData(sfWebRequest $request)
    {
        return $this->sendJson($this->makeModelTypeLabel($request));
    }

    /**
     * @param $object
     * @return array
     */
    private function makeModelTypeLabel($object) {
        if ($object instanceof AgreementModel) {
            $model_type = AgreementModelTypeTable::getInstance()->find($object->getModelTypeId());
        } else {
            $model_type = AgreementModelTypeTable::getInstance()->createQuery()->where('id = ?', $object->getParameter('type_id'))->fetchOne();
        }

        if ($model_type) {
            return array(
                'label' => $model_type->getAgreementType() != 'simple' ? explode(';', $model_type->getFieldDescription()) : 'Макет',
                'is_scenario_record' => $model_type->getAgreementType() != 'simple' ? true : false
            );
        }

        return array();
    }

    /**
     * @param sfWebRequest $request
     * @return string
     */
    public function executeLoadModelCategoryForbiddenMimeTypes(sfWebRequest $request) {
        $forbidden_types_list = array();

        foreach (AgreementModelCategoriesAllowedMimeTypesTable::getInstance()->createQuery()->where('category_id = ?', $request->getParameter('category_id'))->execute() as $mime_type) {
            $forbidden_types_list[] = $mime_type->getMimeType()->getExtension();
        }

        return $this->sendJson($forbidden_types_list);
    }

    /**
     * Удаление заявки дилером
     * @param sfWebRequest $request
     * @return string
     */
    public function executeDeleteModelByDealer(sfWebRequest $request) {
        $model = AgreementModelTable::getInstance()->find($request->getParameter('model_id'));
        if ($model) {
            $model->setIsDeleted(true);
            $model->save();

            $entry = new LogEntry();
            $entry->setArray(array(
                'user_id' => $this->getUser()->getAuthUser()->getId(),
                'login' => $this->getUser()->getAuthUser()->getEmail(),
                'title' => 'Удаление заявки',
                'description' => 'Удаление заявки дилером',
                'icon' => '',
                'object_id' => $model->getId(),
                'object_type' => 'agreement_model',
                'action' => 'model_deleted_by_dealer',
                'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                'message_id' => 0,
                'created_at' => time(),
                'private_user_id' => 0
            ));
            $entry->save();

            return $this->sendJson(array('success' => true));
        }

        return $this->sendJson(array('success' => false));
    }

    /**
     * Восстановление удаленной заявки
     * @param sfWebRequest $request
     * @return string
     */
    public function executeUndoDeleteModelByDealer(sfWebRequest $request) {
        $model = AgreementModelTable::getInstance()->find($request->getParameter('model_id'));
        if ($model) {
            $model->setIsDeleted(false);
            $model->save();

            $entry = new LogEntry();
            $entry->setArray(array(
                'user_id' => $this->getUser()->getAuthUser()->getId(),
                'login' => $this->getUser()->getAuthUser()->getEmail(),
                'title' => 'Отмена удаления заявки',
                'description' => 'Отмена удаления заявки',
                'icon' => '',
                'object_id' => $model->getId(),
                'object_type' => 'agreement_model',
                'action' => 'undo_model_deleted_by_dealer',
                'dealer_id' => $this->getUser()->getAuthUser()->getDealer()->getId(),
                'message_id' => 0,
                'created_at' => time(),
                'private_user_id' => 0
            ));
            $entry->save();

            return $this->sendJson(array('success' => true));
        }

        return $this->sendJson(array('success' => false));
    }

    /**
     * Проверка на наличе даты в календаре
     * @param sfWebRequest $request
     * @return string
     */
    public function executeCheckDateInCalendar(sfWebRequest $request) {
        $dates = CalendarTable::getCalendarDates();

        $result_dates = array();
        foreach ($dates as $date) {
            $elapsed_days = Utils::getElapsedTime(strtotime($date['end_date']) - strtotime($date['start_date']));
            $result_dates[] = date('Y-n-j', strtotime($date['start_date']));

            if ($elapsed_days > 0) {
                for ($inc_day = 1; $inc_day <= $elapsed_days; $inc_day++) {
                    $result_dates[] = date('Y-n-j', strtotime('+'.$inc_day.' days', strtotime($date['start_date'])));
                }
            } else {
                $result_dates[] = date('Y-n-j', strtotime($date['end_date']));
            }

        }

        return $this->sendJson(array('dates' => $result_dates));
    }
}
