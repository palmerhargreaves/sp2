<?php

/**
 *  models_date actions.
 *
 * @package    Servicepool2.0
 * @subpackage comment_stat
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class models_datesActions extends sfActions
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */

    const MOVE_TYPE_ACTIVITIES = 'activity';
    const MOVE_TYPE_DEALERS = 'dealer';
    const MOVE_TYPE_DESIGNER = 'designer';

    const DESIGNER_ROLE = 22;

    function executeIndex(sfWebRequest $request)
    {
    }

    function executeFindModel(sfWebRequest $request)
    {
        $modelsId = explode(',', $request->getParameter('model_id'));
        $this->moveType = $request->getParameter('sbMoveType');

        if (count($modelsId) == 0) {
            $this->models = null;
        } else {
            $this->models = AgreementModelTable::getInstance()
                ->createQuery('m')
                ->select('*')
                ->leftJoin('m.Report r')
                ->leftJoin('m.ModelType mt')
                //->where('r.status = ?', array('accepted'))
                ->whereIn('m.id', $modelsId)
                ->execute();

            $notInActivities = array();
            foreach ($this->models as $model) {
                $notInActivities[] = $model->getActivity()->getId();
            }

            if ($this->models && $this->moveType == self::MOVE_TYPE_ACTIVITIES) {
                $this->activities = ActivityTable::getInstance()
                    ->createQuery()
                    ->select()
                    ->whereNotIn('id', $notInActivities)
                    ->orderBy('id DESC')
                    ->execute();
            } else if ($this->moveType == self::MOVE_TYPE_DEALERS) {
                $this->dealers = DealerTable::getVwDealersQuery()->execute();
            } else if($this->moveType == self::MOVE_TYPE_DESIGNER) {
                $this->designers = UserTable::getInstance()->createQuery()->where('group_id = ?', self::DESIGNER_ROLE)->execute();
            }

            if (count($this->models) == 0) {
                $this->success = false;
                $this->makeChanges = true;

                $this->setTemplate('index');
            }
        }
    }

    function executeShow(sfWebRequest $request)
    {
        $this->setTemplate('index');
    }

    /**
     * Перенос заявок выбранному дилеру
     * @param sfWebRequest $request
     */
    function executeModelMoveToDealer(sfWebRequest $request)
    {
        $modelsIds = $request->getParameter('modelsIds');
        $dealer = $request->getParameter('moveTo');

        if (count($modelsIds) > 0) {
            $query = AgreementModelTable::getInstance()
                ->createQuery('m')
                ->select('*')
                ->leftJoin('m.Report r')
                ->leftJoin('m.ModelType mt')
                ->andWhereIn('m.id', $modelsIds);
            $models = $query->execute();

            foreach ($models as $model) {
                $msg = '';

                if ($dealer != -1) {
                    $from_dealer = DealerTable::getInstance()->find($model->getDealerId());
                    $to_dealer = DealerTable::getInstance()->find($dealer);

                    $msg = sprintf(
                        "<strong>Перенос заявки дилеру:</strong> <br/> Дилер до: %s <br /> Дилер после: %s",
                        sprintf('[%s] %s', substr($from_dealer->getNumber(), -3), $from_dealer->getName()),
                        sprintf('[%s] %s', substr($to_dealer->getNumber(), -3), $to_dealer->getName())
                    );

                    $new_entry = new LogEntry();
                    $new_entry->setArray(
                        array
                        (
                            'user_id' => $this->getUser()->getAuthUser()->getId(),
                            'description' => $msg,
                            'object_id' => $model->getId(),
                            'module_id' => 1,
                            'action' => 'model_move_to_dealer',
                            'object_type' => 'agreement_model',
                            'login' => $this->getUser()->getAuthUser()->getEmail(),
                            'dealer_id' => $model->getDealerId(),
                            'title' => 'Перенос заявки',
                        )
                    );
                    $new_entry->save();

                    //Меняем в приваязки дат, дилера
                    $modelDates = AgreementModelDatesTable::getInstance()->findOneByModelId($model->getId());
                    if ($modelDates) {
                        $modelDates->setDealerId($dealer);
                        $modelDates->save();
                    }

                    $model->setDealerId($dealer);
                    $model->save();
                }
            }

            echo json_encode(
                array
                (
                    'success' => true,
                    'msg' => 'Перенос заявок успешно завершен. Всего заявок: ' . count($models) .
                        '<br/><a href="/backend.php/models_dates">Вернуться к поиску</a>'
                )
            );
        } else {
            echo json_encode(array('success' => false, 'msg' => 'Ошибка при переносе заявок.'));
        }

        return sfView::NONE;
    }

    /**
     * Перенос заявок в другую активность
     * @param sfWebRequest $request
     * @return string
     */
    function executeModelDate(sfWebRequest $request)
    {
        $date = $request->getParameter('modelToDate');
        $modelsIds = $request->getParameter('modelsIds');

        $this->date = $date;
        $this->modelsIds = $modelsIds;
        $this->activity = $request->getParameter('moveTo');

        if (preg_match('#^[0-9]{2}\-[0-9]{2}\-[0-9]{4}$#', $date)) {
            $date = date('Y-m-d H:i:s', strtotime($date . date('H:i:s')));
        } else {
            $date = null;
        }

        if (count($modelsIds) > 0) {
            $query = AgreementModelTable::getInstance()
                ->createQuery('m')
                ->select('*')
                ->leftJoin('m.Report r')
                ->leftJoin('m.ModelType mt')
                //->where('r.status = ?', array('accepted'))
                ->andWhereIn('m.id', $modelsIds);
            $models = $query->execute();

            foreach ($models as $model) {
                $msg = array();

                if ($this->activity != -1) {
                    $msg[] = sprintf(
                        "<strong>Перенес заявки в активность:</strong> <br/> Активность до: %s<br/>  Активность после: %s",
                        $model->getActivityId(),
                        $this->activity
                        );

                    $model->setActivityId($this->activity);

                    //Меняем в приваязки дат, активность
                    $modelDates = AgreementModelDatesTable::getInstance()->findOneByModelId($model->getId());
                    if ($modelDates) {
                        $modelDates->setActivityId($this->activity);
                        $modelDates->save();
                    }
                }

                $entry = null;
                if(!is_null($date)) {
                    $updated = $model->getUpdatedAt();

                    $query = LogEntryTable::getInstance()
                        ->createQuery()
                        ->where('object_id = ?', array($model->getId()))
                        ->orderBy('id DESC');

                    if ($model->isConcept()) {
                        $query->andWhere('(object_type = ? or object_type = ?) and icon = ? and action = ? and private_user_id = ?', array('agreement_concept_report',  'agreement_special_concept_report_regional_manager', 'clip', 'edit', 0));
                    } else {
                        $query->andWhere('object_type = ? and icon = ? and action = ? and private_user_id = ?', array('agreement_report', 'clip', 'edit', 0));
                    }
                    $entry = $query->fetchOne();

                    if ($entry){
                        $updated = $entry->getCreatedAt();

                        $entry->setCreatedAt($date);
                        $entry->save();
                    }

                    $msg[] = sprintf(
                        "<strong>Перенос заявки по дате:</strong> <br/> Дата до: %s <br /> Дата после: %s <br/> Дата изменений: %s",
                        sprintf('%s', $updated),
                        sprintf('%s', $date),
                        date('d-m-Y H:i:s')
                    );

                    $model->setUpdatedAt($date);

                    $report = $model->getReport();
                    if ($report && $report->getId() != null) {
                        $report->setAcceptDate($date);
                        $report->save();
                    }
                }

                if (!empty($msg)) {
                    $new_entry = new LogEntry();
                    $new_entry->setArray(
                        array
                        (
                            'user_id' => $this->getUser()->getAuthUser()->getId(),
                            'description' => implode('<br/>', $msg),
                            'object_id' => $model->getId(),
                            'module_id' => 1,
                            'action' => 'model_move_to_activity_date',
                            'object_type' => $entry ? $entry->getObjectType() : 'agreement_model',
                            'login' => $this->getUser()->getAuthUser()->getEmail(),
                            'dealer_id' => $model->getDealerId(),
                            'title' => 'Перенос заявки',
                        )
                    );
                    $new_entry->save();
                }

                $model->save();

                $this->success = true;
            }

            echo json_encode(
                array
                (
                    'success' => true,
                    'msg' => 'Перенос заявок успешно завершен. Всего заявок: ' . count($models) .
                        '<br/><a href="/backend.php/models_dates">Вернуться к поиску</a>'
                )
            );
        } else {
            echo json_encode(array('success' => false, 'msg' => 'Ошибка при переносе заявок.'));
        }

        return sfView::NONE;
    }

    /**
     * Перенос заявки дизайнеру
     */
    public function executeModelMoveToDesigner(sfWebRequest $request) {
        $modelsIds = $request->getParameter('modelsIds');
        $designer = $request->getParameter('moveTo');

        if (count($modelsIds) > 0) {
            $query = AgreementModelTable::getInstance()
                ->createQuery('m')
                ->select('*')
                ->leftJoin('m.Report r')
                ->leftJoin('m.ModelType mt')
                ->andWhereIn('m.id', $modelsIds);
            $models = $query->execute();

            foreach ($models as $model) {

                if ($designer != -1) {
                    $check_designer = AgreementModelCheckByDesignerTable::getInstance()->createQuery()->where('model_id = ?', $model->getId())->fetchOne();
                    if (!$check_designer) {
                        continue;
                    }

                    $from_designer = UserTable::getInstance()->createQuery()->where('id = ?', $check_designer->getUser()->getId())->fetchOne();
                    $to_designer = UserTable::getInstance()->createQuery()->where('id = ?', $designer)->fetchOne();

                    $msg = sprintf(
                        "<strong>Перенос заявки дизайнеру:</strong> <br/> Дизайнер до: %s <br /> Дизайнер после: %s",
                        sprintf('%s %s', $from_designer->getSurname(),$from_designer->getName()),
                        sprintf('%s %s', $to_designer->getSurname(), $to_designer->getName())
                    );

                    $new_entry = new LogEntry();
                    $new_entry->setArray(
                        array
                        (
                            'user_id' => $this->getUser()->getAuthUser()->getId(),
                            'description' => $msg,
                            'object_id' => $model->getId(),
                            'module_id' => 1,
                            'action' => 'model_move_to_designer',
                            'object_type' => 'agreement_model',
                            'login' => $this->getUser()->getAuthUser()->getEmail(),
                            'dealer_id' => $model->getDealerId(),
                            'title' => 'Перенос заявки дизайнеру',
                        )
                    );
                    $new_entry->save();

                    $check_designer->setUser($to_designer);
                    $check_designer->save();
                }
            }

            echo json_encode(
                array
                (
                    'success' => true,
                    'msg' => 'Перенос заявок успешно завершен. Всего заявок: ' . count($models) .
                        '<br/><a href="/backend.php/models_dates">Вернуться к поиску</a>'
                )
            );
        } else {
            echo json_encode(array('success' => false, 'msg' => 'Ошибка при переносе заявок.'));
        }

        return sfView::NONE;
    }

    public function executeModelHistoryMove(sfWebRequest $request) {
        $model_id = $request->getParameter('model_id');

        $query = LogEntryTable::getInstance()->createQuery()
            ->whereIn('action', array('model_move_to_activity_date', 'model_move_to_dealer', 'model_move_to_designer'))
            ->orderBy('id DESC');

        if (!empty($model_id)) {
            $query->andWhere('object_id = ?', $model_id);
        }

        $this->history_list = $query->execute();
    }
}
