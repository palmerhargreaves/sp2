<?php

/**
 * activity_extended_statistic actions.
 *
 * @package    Servicepool2.0
 * @subpackage activity_extended_statistic
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activity_extended_statisticActions extends sfActions
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    const MAIL_TEMPLATE_DEALER_NAME = '{dealer_name}';
    const MAIL_TEMPLATE_DEALER_DATE = '{date}';
    const MAIL_TEMPLATE_DEALER_DATE_MONTH = '{date_month}';

    function executeIndex(sfWebRequest $request)
    {
        $this->outputSections();
        $this->outputFields();

        $this->activities = ActivityTable::getInstance()->createQuery()->where('allow_extended_statistic = ?', true)->orderBy('position ASC')->execute();
    }

    function executeLoad(sfWebRequest $request)
    {
        $this->outputSections(null, $request->getParameter('id'));
        $this->outputFields(null, $request->getParameter('id'));
        $this->outputCertificatedData($request->getParameter('id'));

        $this->mailDealerList = ActivityDealerMailsTable::getInstance()->createQuery()->where('activity_id = ?', $request->getParameter('id'))->orderBy('id ASC')->execute();

        $this->statistic = new ActivityExtendedStatisticsBuilder(array('activity' => $request->getParameter('id')));
        $this->activity = $request->getParameter('id');
    }

    //Add new section
    function executeAddSection(sfWebRequest $request)
    {
        $activityId = $request->getParameter('activityId');
        $sectionName = $request->getParameter('txtSectionName');
        $sectionParent = $request->getParameter('sbSectionParent');

        $item = new ActivityExtendedStatisticSections();
        $item->setArray(array('header' => $sectionName,
            'parent_id' => $sectionParent,
            'activity_id' => $activityId,
            'status' => 1));
        $item->save();

        $this->outputSections();
    }

    //Begin edit section
    function executeBeginEditSection(sfWebRequest $request)
    {
        $this->section = ActivityExtendedStatisticSectionsTable::getInstance()->find($request->getParameter('id'));

        $this->outputSections($request->getParameter('id'));
    }

    //Edit section
    function executeEditSection(sfWebRequest $request)
    {
        $sectionName = $request->getParameter('txtSectionName');
        $sectionParent = $request->getParameter('sbSectionParent');

        $item = ActivityExtendedStatisticSectionsTable::getInstance()->find($request->getParameter('id'));
        if ($item) {
            $item->setArray(array('header' => $sectionName,
                'parent_id' => $sectionParent));
            $item->save();
        }

        $this->outputSections();
    }

    //Delete section
    function executeDeleteSection(sfWebRequest $request)
    {
        ActivityExtendedStatisticSectionsTable::getInstance()->find($request->getParameter('id'))->delete();

        $this->outputSections();
    }

    //Sections list
    function executeSectionsList(sfWebRequest $request)
    {

    }

    //Add new field
    function executeAddField(sfWebRequest $request)
    {
        $field = new ActivityExtendedStatisticFields();

        $calcFields = $request->getParameter('calcFields');

        $field->setArray(array('header' => $request->getParameter('txtFieldName'),
            'description' => $request->getParameter('txtFieldDescription'),
            'parent_id' => $request->getParameter('sbFieldParent'),
            'activity_id' => $request->getParameter('activityId'),
            'value_type' => $request->getParameter('sbFieldType'),
            'status' => 1));
        $field->save();

        if (!empty($calcFields) && is_array($calcFields)) {
            foreach ($calcFields as $calcField) {
                $itemCalcField = new ActivityExtendedStatisticFieldsCalculated();

                $itemCalcField->setArray(
                    array
                    (
                        'parent_field' => $field->getId(),
                        'calc_field' => $calcField,
                        'calc_type' => $request->getParameter('sbCalcFieldsAction'),
                        'activity_id' => $request->getParameter('activityId')
                    )
                );
                $itemCalcField->save();
            }
        }

        $this->outputSections(null, $request->getParameter('activityId'));
        $this->outputFields(null, $request->getParameter('activityId'));
    }

    //Begin add field
    function executeBeginEditField(sfWebRequest $request)
    {

    }

    //Edit field
    function executeEditField(sfWebRequest $request)
    {

    }

    //Delete field
    function executeDeleteField(sfWebRequest $request)
    {
        $field = ActivityExtendedStatisticFieldsTable::getInstance()->find($request->getParameter('id'));
        $activity = null;

        if ($field) {
            ActivityExtendedStatisticFieldsCalculatedTable::getInstance()->createQuery()->where('parent_field = ?', $field)->delete()->execute();

            $activity = $field->getActivityId();
            $field->delete();
        }

        $this->outputSections(null, $activity);
        $this->outputFields(null, $activity);
    }

    //Fields list
    function executeFieldsList(sfWebRequest $request)
    {

    }

    function outputSections($id = null, $parentId = null)
    {
        $query = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->orderBy('id DESC');

        if (!empty($id))
            $query->andWhere('id != ?', $id);

        if (!empty($parentId))
            $query->andWhere('activity_id = ?', $parentId);

        $this->sections = $query->execute();
    }

    function outputFields($id = null, $parentId = null)
    {
        $query = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->orderBy('id DESC');

        if (!empty($id))
            $query->andWhere('id != ?', $id);

        if (!empty($parentId))
            $query->andWhere('activity_id = ?', $parentId);

        $this->fields = $query->execute();
    }

    function outputCalculatedFields(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $act = $request->getParameter('act');

        $result = array('fields' => array(), 'act' => $act);
        foreach ($ids as $id) {
            $field = ActivityExtendedStatisticFieldsTable::getInstance()->find($id);

            if ($field)
                $result['fields'][] = $field;
        }

        $this->fields = $result;
    }

    function outputCertificatedData($parentId)
    {
        $this->certificateItems = AgreementModelUserSettingsTable::getInstance()->createQuery()->where('activity_id = ?', $parentId)->execute();
    }

    function executeChangeCertificateDate(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $days = $request->getParameter('days');

        $item = AgreementModelUserSettingsTable::getInstance()->find($id);
        if ($item) {
            $toDate = date('Y-m-d', strtotime('+' . $days . ' days', strtotime(date('Y-m-d'))));

            $item->setCertificateEnd($toDate);
            $item->save();
        }

        $this->item = $item;
    }

    function executeAddDealerToMailList(sfWebRequest $request)
    {
        $item = new ActivityDealerMails();

        $item->setDealerId($request->getParameter('id'));
        $item->setActivityId($request->getParameter('activity'));
        $item->setDateTo(date('d-m-Y'));

        $item->save();

        $this->items = ActivityDealerMailsTable::getInstance()->createQuery()->where('activity_id = ?', $request->getParameter('activity'))->orderBy('id DESC')->execute();
    }

    function executeRemoveDealerFromMailList(sfWebRequest $request)
    {
        ActivityDealerMailsTable::getInstance()->find($request->getParameter('id'))->delete();

        return sfView::NONE;
    }

    function executeSendDealersMail(sfWebRequest $request)
    {
        $dealers = $request->getParameter('dealers');

        $dealersIds = array();
        if (!empty($dealers)) {
            $dealers = explode(',', $dealers);

            foreach ($dealers as $id) {
                $item = ActivityDealerMailsTable::getInstance()->createQuery()->select()
                    ->where('id = ? and activity_id = ?', array($id, $request->getParameter('activity')))
                    ->fetchOne();
                if ($item)
                    $dealersIds[] = $item;
            }
        } else {
            $dealers = ActivityDealerMailsTable::getInstance()->createQuery()->select()->orderBy('id ASC')->execute();
            foreach ($dealers as $key => $item)
                $dealersIds[] = $item;
        }

        $msgTemplate = $request->getParameter('msg');
        $totalSended = 0;

        foreach ($dealersIds as $item) {
            $msgText = $msgTemplate;

            if (strrpos($msgText, self::MAIL_TEMPLATE_DEALER_NAME) !== false) {
                $msgText = str_replace(self::MAIL_TEMPLATE_DEALER_NAME, $item->getDealer()->getName(), $msgText);
            }

            if (strrpos($msgText, self::MAIL_TEMPLATE_DEALER_DATE) !== false) {
                $msgText = str_replace(self::MAIL_TEMPLATE_DEALER_DATE, $item->getDateTo(), $msgText);
            }

            if (strrpos($msgText, self::MAIL_TEMPLATE_DEALER_DATE_MONTH) !== false) {
                $msgText = str_replace(self::MAIL_TEMPLATE_DEALER_DATE_MONTH, date("m", strtotime($item->getDateTo())), $msgText);
            }

            $userDealers = DealerUserTable::getInstance()
                ->createQuery()
                ->where('dealer_id = ?',
                    array($item->getDealer()->getId()))
                ->orderBy('id DESC')
                ->execute();

            foreach ($userDealers as $userDealer) {
                $user = $userDealer->getUser();

                if ($user->getAllowReceiveMails()) {
                    $mail = new ActivityDealersSendMail($user, $msgText);
                    if ($mail)
                        $mail->setPriority(1);

                    sfContext::getInstance()->getMailer()->send($mail);
                    $totalSended++;
                }
            }

        }

        if ($totalSended) {
            $sendItem = new ActivityDealerMailsSends();
            $sendItem->setMsg($msgTemplate);
            $sendItem->setActivityId($request->getParameter('activity'));
            $sendItem->save();
        }

        $this->sendMailTemplate = ActivityDealerMailsSendsTable::getInstance()->createQuery()->orderBy('id DESC')->limit(5)->execute();
    }

    public function executeChangeDealerMailDate(sfWebRequest $request)
    {
        $item = ActivityDealerMailsTable::getInstance()->find($request->getParameter('id'));
        if ($item) {
            $item->setDateTo($request->getParameter('data'));
            $item->save();
        }

        return sfView::NONE;
    }

    public function executeChangeFieldRequiredStatus(sfWebRequest $request)
    {
        $fieldId = $request->getParameter('fieldId');

        $field = ActivityExtendedStatisticFieldsTable::getInstance()->find($fieldId);
        if ($field) {
            $field->setRequired($request->getParameter('status'));
            $field->save();
        }

        return sfView::NONE;
    }

    public function executeConceptAdd(sfWebRequest $request)
    {
        $concept = $request->getParameter('concept');
        $dealerId = $request->getParameter('dealer_id');
        $activityId = $request->getParameter('activity_id');

        if (ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()->where('dealer_id = ? and concept_id = ?', array($dealerId, $concept))->count() == 0) {
            $datas = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()->where('dealer_id = ?', $dealerId)->execute();
            foreach ($datas as $data) {
                $data->setConceptId($concept);
                $data->save();
            }
        }

        $this->dealerId = $dealerId;
        $this->activityId = $activityId;
        $this->dealerConcepts = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()->where('dealer_id = ?', $dealerId)->groupBy('concept_id')->execute();
    }

    public function executeDeleteConcept(sfWebRequest $request)
    {
        $concept = $request->getParameter('concept');

        $items = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()->where('concept_id = ?', $concept)->execute();
        foreach ($items as $item) {
            $item->setConceptId(0);
            $item->save();
        }

        return sfView::NONE;
    }

    public function executeExportExtendedStatisticToExcel(sfWebRequest $request)
    {
        $url = ActivityExtendedStatisticsBuilder::makeExportFile($request);
        echo $url;

        return sfView::NONE;
    }

    public function executeServiceClinicConfig(sfWebRequest $request)
    {
        $this->activities = ActivityTable::getInstance()
            ->createQuery('a')
            ->where('allow_extended_statistic = ?', true)
            ->innerJoin('a.ServiceClinicSections ss')
            ->orderBy('position ASC')
            ->execute();
    }

    public function executeServiceClinicMakeCopy(sfWebRequest $request)
    {
        $from_activity_id = $request->getParameter('from_activity');
        $to_activity_id = $request->getParameter('to_activity');

        $sections_ids = explode(':', $request->getParameter('headers_list_ids'));
        $fields_ids = explode(':', $request->getParameter('fields_list_ids'));

        $sections_list = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->whereIn('id', $sections_ids)->andWhere('activity_id = ?', $from_activity_id)->execute();
        $fields_list = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->whereIn('id', $fields_ids)->andWhere('activity_id = ?', $from_activity_id)->execute();

        $total_copy = 0;
        $saved_sections = array();
        /**
         * Make sections copy
         */
        foreach ($sections_list as $section) {
            $section_item = new ActivityExtendedStatisticSections();
            $section_item->setArray(
                array(
                    'header' => $section->getHeader(),
                    'parent_id' => -1,
                    'status' => $section->getStatus(),
                    'activity_id' => $to_activity_id
                )
            );
            $section_item->save();

            $saved_sections[$section->getId()] = $section_item->getId();

            if ($section->getParentId() != -1) {
                $parent_section = ActivityExtendedStatisticSectionsTable::getInstance()->find($section->getParentId());
                if ($parent_section) {
                    $copy_parent_section = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()
                        ->where('header = ? and activity_id = ?', array($parent_section->getHeader(), $to_activity_id))->fetchOne();
                    if ($copy_parent_section) {
                        $section_item->setParentId($copy_parent_section->getId());
                        $section_item->save();
                    }
                }
            }

            $total_copy++;
        }

        /**
         * Make fields copy
         */

        $fields_copy = array();
        foreach ($fields_list as $field) {
            $field_item = new ActivityExtendedStatisticFields();
            $field_item->setArray(
                array(
                    'header' => $field->getHeader(),
                    'value_type' => $field->getValueType(),
                    'activity_id' => $to_activity_id,
                    'parent_id' => isset($saved_sections[$field->getParentId()]) ? $saved_sections[$field->getParentId()] : -1,
                    'status' => $field->getStatus(),
                    'description' => $field->getDescription(),
                    'required' => $field->getRequired(),
                    'position' => $field->getPosition()
                )
            );
            $field_item->save();

            $fields_copy[$field->getId()] = $field_item->getId();

            $total_copy++;
        }

        /**
         * Copy calculated files
         */
        $calc_fields_list = ActivityExtendedStatisticFieldsCalculatedTable::getInstance()->createQuery()->where('activity_id = ?', $from_activity_id)->orderBy('id ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($calc_fields_list as $calc_field) {
            $calc_item = new ActivityExtendedStatisticFieldsCalculated();

            if (array_key_exists($calc_field['parent_field'], $fields_copy) && array_key_exists($calc_field['calc_field'], $fields_copy)) {
                $calc_item->setArray(
                    array(
                        'parent_field' => $fields_copy[$calc_field['parent_field']],
                        'calc_field' => $fields_copy[$calc_field['calc_field']],
                        'calc_type' => $calc_field['calc_type'],
                        'activity_id' => $to_activity_id
                    )
                );
                $calc_item->save();
            }
        }

        if ($total_copy == 0) {
            $this->getResponse()->setContentType('application/json');
            $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Ошибка копирования полей.')));
        } else {
            $this->getResponse()->setContentType('application/json');
            $this->getResponse()->setContent(json_encode(array('success' => true, 'message' => 'Копирование данных успешно завершено.')));
        }

        return sfView::NONE;
    }

    public function executeServiceClinicCopyGetData(sfWebRequest $request)
    {
        $activity_id = $request->getParameter('activity');

        $result = ActivityTable::getInstance()
            ->createQuery()
            ->where('allow_extended_statistic = ? and id != ?', array(true, $activity_id))
            ->orderBy('position ASC')
            ->execute();

        $this->activities = array();
        foreach ($result as $activity) {
            if ($activity->getServiceClinicSections()->count() == 0) {
                $this->activities[] = $activity;
            }
        }

        $this->service_clinic_headers = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->orderBy('id ASC')->execute();
        $this->service_clinic_fields = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->orderBy('position ASC')->execute();
    }
}
