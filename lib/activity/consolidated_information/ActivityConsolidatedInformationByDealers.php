<?php

require_once (sfConfig::get('app_root_dir').'lib/jpgraph/jpgraph.php');
require_once (sfConfig::get('app_root_dir').'lib/jpgraph/jpgraph_pie.php');
require_once (sfConfig::get('app_root_dir').'lib/jpgraph/jpgraph_pie3d.php');

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 01.11.2018
 * Time: 11:38
 */
class ActivityConsolidatedInformationByDealers
{
    const FIELDS_PER_PAGE = 12;

    //Список активностей для выгрузки
    private $_activities = array();

    //Список кварталов
    private $_quarters = array();

    //Список дилеров
    private $_dealers = array();

    //Региональный менеджер (Все или один)
    private $_regional_manager = null;

    private $_year = 0;

    private $_request = null;

    public function __construct(sfWebRequest $request)
    {
        $this->_activities = $request->getParameter('activities');
        $this->_quarters = $request->getParameter('quarters');
        $this->_dealers = $request->getParameter('dealers');
        $this->_regional_manager = $request->getParameter('regional_manager');

        $this->_year = date('Y');

        $this->_request = $request;
    }

    public function getData()
    {
        $manager_dealers_list = array();

        //$active_activities_list = ActivityTable::getInstance()->createQuery()->select('id, name')->where('finished = ?', false)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        //Проходим по всем активным дилерам и собираем информацию по общей сумме заявок за выбранный период
        $dealers_query = DealerTable::getActiveDealersList();
        $active_dealers_ids = array_map(function ($item) {
            return $item['id'];
        }, $dealers_query->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $max_models_cost = 0;
        $dealers_total_cash_by_models = array();
        foreach ($this->_quarters as $quarter) {
            //Учитываем квартал
            if (!array_key_exists($quarter, $dealers_total_cash_by_models)) {
                $dealers_total_cash_by_models[$quarter] = array();
            }

            //Проходим по всем дилерам для получения общей суммы учтенных заявок за выбранный период (квартал и год)
            foreach ($active_dealers_ids as $dealer_id) {

                $models_list = AgreementModelTable::getInstance()->createQuery('am')
                    ->select('id, cost, dealer_id, model_category_id')
                    ->innerJoin('am.Report r')
                    ->andWhere('am.dealer_id = ?', $dealer_id)
                    //Удаленные заявки не выбираем
                    ->andWhere('is_deleted = ?', false)
                    ->andWhereIn('activity_id', $this->_activities)
                    ->andWhere('(am.status = ? and r.status = ?)', array('accepted', 'accepted'))
                    //->andWhere('(year(created_at) = ? or year(created_at) = ?)', array($this->_year, $this->_year - 1))
                    ->andWhere('(year(created_at) = ?)', array($this->_year))
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                //Получаемя список заявок с привязкой к индексу заявки и стоимости заявки
                $models_list_with_cost = array();
                foreach ($models_list as $model) {
                    //Учитываем для категории заявки общую сумму
                    if (!array_key_exists($model['model_category_id'], $dealers_total_cash_by_models[$quarter])) {
                        $dealers_total_cash_by_models[$quarter][$model['model_category_id']] = array('total_models_cost' => 0, 'total_models' => 0, 'percent' => 0);
                    }

                    $models_list_with_cost[$model['id']] = array('cost' => floatval($model['cost']), 'model_category_id' => $model['model_category_id']);
                }

                //Получаем индексы заявок для получения точной даты выполнения заявки
                $models_ids = array_map(function($item) {
                    return $item['id'];
                }, $models_list);

                //Если нет заявок переходим к сл. дилеру
                if (empty($models_list)) {
                    continue;
                }

                //Список выполненных заявок с корректными датами
                $models_dates = Utils::getModelDateFromLogEntryWithYear($models_ids);

                foreach ($models_dates as $model_item) {
                    if (array_key_exists($model_item['object_id'], $models_list_with_cost)) {
                        //Дата выполнения заявки
                        $model_date = date('Y-m-d H:i:s', D::calcQuarterData($model_item['created_at']));

                        //Получаем квартал выполнения заявки и год
                        $model_quarter = D::getQuarter($model_date);
                        $model_year = D::getYear($model_date);

                        //Делаем проверку на квартал и год выполнения
                        if ($model_quarter == $quarter && $model_year == $this->_year) {
                            $dealers_total_cash_by_models[$quarter][$models_list_with_cost[$model_item['object_id']]['model_category_id']]['total_models_cost'] += $models_list_with_cost[$model_item['object_id']]['cost'];
                            $dealers_total_cash_by_models[$quarter][$models_list_with_cost[$model_item['object_id']]['model_category_id']]['total_models']++;

                            //Максимальная сумма заявок по категориям
                            if ($dealers_total_cash_by_models[$quarter][$models_list_with_cost[$model_item['object_id']]['model_category_id']]['total_models_cost'] > $max_models_cost) {
                                $max_models_cost = $dealers_total_cash_by_models[$quarter][$models_list_with_cost[$model_item['object_id']]['model_category_id']]['total_models_cost'];
                            }
                        }
                    }
                }
            }
        }

        //Вычисяляем проценты от максимальной суммы заявок
        if ($max_models_cost > 0) {
            foreach ($dealers_total_cash_by_models as $quarter => $model_item) {
                foreach ($model_item as $model_category_id => $model_category) {
                    $dealers_total_cash_by_models[$quarter][$model_category_id]['percent'] = $model_category['total_models_cost'] * 100 / $max_models_cost;
                }
            }
        }

        //Аккамулируем сумму по категориям заявок для каждого дилера с учетом квартала
        $dealer_total_models_cost_by_categories = array();

        //Получаем список региональных менеджеров
        foreach ($this->getRegionalManager() as $manager) {
            if (!array_key_exists($manager->getId(), $manager_dealers_list)) {
                $manager_dealers_list[$manager->getId()] = array(
                    1 => array('dealers' => array()),
                    2 => array('dealers' => array()),
                    3 => array('dealers' => array()),
                    4 => array('dealers' => array()),
                    'manager' => $manager
                );
            }

            //Получаем список дилеров
            $dealers_list = DealerTable::getInstance()->createQuery()
                ->where('status = ?', true)
                ->andWhereIn('id', $this->_dealers)
                ->andWhere('(regional_manager_id = ? or nfz_regional_manager_id = ?)', array($manager->getId(), $manager->getId()))
                ->execute();

            //Вычисляем бюджеты для дилеров
            $dealers_budgets = array();
            foreach ($dealers_list as $dealer) {
                if (!array_key_exists($dealer->getId(), $dealers_budgets)) {
                    $dealers_budgets[$dealer->getId()] = array(
                        "quarters" => array(
                            1 => array(
                                "plan" => 0,
                                "fact" => 0,
                            ),
                            2 => array(
                                "plan" => 0,
                                "fact" => 0,
                            ),
                            3 => array(
                                "plan" => 0,
                                "fact" => 0,
                            ),
                            4 => array(
                                "plan" => 0,
                                "fact" => 0,
                            )
                        ),
                        "all_year" => array(
                            "plan" => 0,
                            "fact" => 0,
                            "completed" => 0,
                            "left_to_complete" => 0
                        )
                    );
                }
                $budget_calculator = new RealBudgetCalculator($dealer, $this->_year);
                $dealer_budget_real = $budget_calculator->calculate();
                $dealer_budget_plan = $budget_calculator->getPlanBudget();

                //Заполняем для дилера данные по бюджету (план и факт)
                //Вычисляем общий бюджет, сколько выполнено и % и сколько осталось выполнить
                $total_plan = 0;
                $total_fact = 0;
                for ($quarter = 1; $quarter <= 4; $quarter++) {
                    if (isset($dealer_budget_plan[$quarter])) {
                        $dealers_budgets[$dealer->getId()]["quarters"][$quarter]["plan"] += $dealer_budget_plan[$quarter];
                        $total_plan += $dealer_budget_plan[$quarter];
                    }

                    if (isset($dealer_budget_real[$quarter])) {
                        $dealers_budgets[$dealer->getId()]["quarters"][$quarter]["fact"] += $dealer_budget_real[$quarter];
                        $total_fact += $dealer_budget_real[$quarter];
                    }
                }

                //Общие суммы
                $dealers_budgets[$dealer->getId()]["all_year"]["plan"] = $total_plan;
                $dealers_budgets[$dealer->getId()]["all_year"]["fact"] = $total_fact;
                $dealers_budgets[$dealer->getId()]["all_year"]["completed"] = $total_plan != 0 ? $total_fact * 100 / $total_plan : 0;
                $dealers_budgets[$dealer->getId()]["all_year"]["left_to_complete"] = $total_fact < $total_plan ? $total_plan - $total_fact : $total_plan;
            }

            foreach ($this->_quarters as $main_quarter) {

                //Полачем список обязательных активностей по кварталу и году
                $slots = QuartersSlotsTable::getInstance()->createQuery()->where('quarter = ? and year = ?', array( $main_quarter, $this->_year ))->execute();

                $mandatory_activities_list = array();
                foreach ($slots as $slot) {
                    $slot_activities = $slot->getSlotActivities();

                    foreach ($slot_activities as $activity) {
                        if (!in_array($activity->getActivityId(), $mandatory_activities_list)) {
                            $mandatory_activities_list[] = $activity->getActivityId();
                        }
                    }
                }

                foreach ($dealers_list as $dealer) {
                    //Заполняем основную информацию по дилерам
                    if (!array_key_exists($dealer->getId(), $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"])) {
                        $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()] = array(
                            "dealer" => $dealer,
                            "budget" => array(
                                "quarters" => array(
                                    1 => array(
                                        "plan" => 0,
                                        "fact" => 0,
                                    ),
                                    2 => array(
                                        "plan" => 0,
                                        "fact" => 0,
                                    ),
                                    3 => array(
                                        "plan" => 0,
                                        "fact" => 0,
                                    ),
                                    4 => array(
                                        "plan" => 0,
                                        "fact" => 0,
                                    )
                                ),
                                "all_year" => array(
                                    "plan" => 0,
                                    "fact" => 0,
                                    "completed" => 0,
                                    "left_to_complete" => 0
                                )
                            ),
                            "activities" => array(),
                            "not_work_with_activity" => array(),
                            "completed_statistics" => array()
                        );

                        //Заполняем для дилера данные по бюджету (план и факт)
                        //Вычисляем общий бюджет, сколько выполнено и % и сколько осталось выполнить
                        for ($quarter = 1; $quarter <= 4; $quarter++) {
                            $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["budget"]["quarters"][$quarter]["plan"] = $dealers_budgets[$dealer->getId()]["quarters"][$quarter]["plan"];
                            $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["budget"]["quarters"][$quarter]["fact"] = $dealers_budgets[$dealer->getId()]["quarters"][$quarter]["fact"];
                        }

                        //Общие суммы
                        $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["budget"]["all_year"]["plan"] = $dealers_budgets[$dealer->getId()]["all_year"]["plan"];
                        $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["budget"]["all_year"]["fact"] = $dealers_budgets[$dealer->getId()]["all_year"]["fact"];
                        $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["budget"]["all_year"]["completed"] = $dealers_budgets[$dealer->getId()]["all_year"]["completed"];
                        $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["budget"]["all_year"]["left_to_complete"] = $dealers_budgets[$dealer->getId()]["all_year"]["left_to_complete"];

                        $activities_ids = array_map(function($item) {
                            return $item['activity_id'];
                        }, AgreementModelTable::getInstance()->createQuery()->select('activity_id')->where('dealer_id = ?', $dealer->getId())
                            ->andWhere('year(created_at) = ?', $this->_year)
                            ->groupBy('activity_id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

                        //Получаем подробную информацию по активностям дилера за текущий год
                        $activities_ids = array_merge($activities_ids, $mandatory_activities_list);
                        $activities = ActivityTable::getInstance()->createQuery()->whereIn('id', $activities_ids)->orderBy('id DESC')->execute();
                        foreach ($activities as $activity) {
                            if (!array_key_exists($activity->getId(), $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["activities"])) {
                                $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["activities"][$activity->getId()] = array(
                                    'name' => $activity->getName(),
                                    'status' => $activity->getStatusBudgetPointsByQuarter($dealer->getId(), $main_quarter, false, $this->_year),
                                    'mandatory_activity' => in_array($activity->getId(), $mandatory_activities_list),
                                    'company_name' => $activity->getCompanyType()->getName()
                                );
                            }
                        }

                        //Определяем выполнения статистики
                        foreach ($this->_activities as $activity_id) {
                            $statistic_status = ActivityExtendedStatisticStepStatusTable::getInstance()->createQuery()
                                ->select('concept_id, activity_id')
                                ->where('activity_id = ? and dealer_id = ? and year = ? and quarter = ? and status = ?',
                                    array
                                    (
                                        $activity_id,
                                        $dealer->getId(),
                                        $this->_year,
                                        $main_quarter,
                                        true
                                    ))
                                ->groupBy('concept_id')
                                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                            //Если статистика заполнена по кварталу и году
                            if (!empty($statistic_status)) {

                                $activity_statistic_fields_count = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->count();
                                $fields_per_page = self::FIELDS_PER_PAGE;
                                $total_pages = $activity_statistic_fields_count > $fields_per_page ? ceil($activity_statistic_fields_count / $fields_per_page) : 1;

                                foreach ($statistic_status as $status) {
                                    if (!array_key_exists($status['concept_id'], $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["completed_statistics"])) {

                                        //Проверяем на наличие акитивности в финальной выгрузке и заполняем данные по активности
                                        if (array_key_exists($status['activity_id'], $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["activities"])) {
                                            $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["completed_statistics"][$status['concept_id']] = array(
                                                'activity_name' => $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["activities"][$status['activity_id']]['name'],
                                                'activity_company_name' => $manager_dealers_list[$manager->getId()][$main_quarter]["dealers"][$dealer->getId()]["activities"][$status['activity_id']]['company_name'],
                                                'total_pages' => $total_pages,
                                                'activity_id' => $activity_id,
                                                'quarter' => $main_quarter,
                                                'year' => $this->_year
                                            );
                                        }
                                    }
                                }


                                //Получаем список выполненных заявок по дилеру и периоду
                                $completed_models_factory = DealerStatisticFactory::getInstance()->getDealerStatistic('completed',
                                    array
                                    (
                                        'request' => null,
                                        'default_filter' => array
                                        (
                                            'dealer_id' => $dealer->getId(),
                                            'quarter' => $main_quarter,
                                            'year' => $this->_year,
                                            'activity' => $activity_id
                                        )
                                    )
                                );

                                $completed_models = $completed_models_factory->getModelsList();
                                foreach ($completed_models as $model) {
                                    if (!array_key_exists($dealer->getId(), $dealer_total_models_cost_by_categories)) {
                                        $dealer_total_models_cost_by_categories[$dealer->getId()] = array();
                                    }

                                    if (!array_key_exists($main_quarter, $dealer_total_models_cost_by_categories[$dealer->getId()])) {
                                        $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter] = array();
                                    }

                                    if (!array_key_exists($activity_id, $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter])) {
                                        $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id] = array();
                                    }

                                    if (!array_key_exists($model['model']->getModelCategoryId(), $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id])) {
                                        $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id][$model['model']->getModelCategoryId()] = array('total_models' => 0, 'total_cost' => 0, 'percent' => 0);
                                    }

                                    $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id][$model['model']->getModelCategoryId()]['total_cost'] += $model['model']->getCost();
                                    $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id][$model['model']->getModelCategoryId()]['total_models']++;

                                    if ($max_models_cost > 0) {
                                        $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id][$model['model']->getModelCategoryId()]['percent'] = $dealer_total_models_cost_by_categories[$dealer->getId()][$main_quarter][$activity_id][$model['model']->getModelCategoryId()]['total_cost'] * 100 / $max_models_cost;
                                    }
                                }

                                unset($completed_models_factory);
                            }
                        }
                    }
                }
            }
        }

        $graph_ticks_values = array();

        $tick_step_value = ceil($max_models_cost) / 10;
        $tick_next_value = 0;
        for($tick_index = 0; $tick_index <= 10; $tick_index++) {
            $graph_ticks_values[] = $tick_next_value;
            $tick_next_value += $tick_step_value;
        }

        return array('managers_dealers_data' => $manager_dealers_list, 'graph_tick_values' => $graph_ticks_values, 'dealer_total_models_cost_by_categories' => $dealer_total_models_cost_by_categories, 'dealers_total_cost' => $dealers_total_cash_by_models);
    }

    /**
     * Получаем список рег. менеджеров для выборки
     */
    private function getRegionalManager()
    {
        $regional_managers_list = array();

        $regional_manager_id = $this->_request->getParameter('regional_manager');

        if ($regional_manager_id == 999) {
            foreach (UserTable::getInstance()->createQuery('u')
                         ->innerJoin('u.Group g')
                         ->where('u.group_id = ?', User::USER_GROUP_REGIONAL_MANAGER)
                         ->andWhere('u.active = ?', true)
                         ->andWhere('u.company_type = ? and u.company_department != ?', array('regional_manager', 0))
                         ->execute() as $manager) {
                $regional_managers_list[] = $manager->getNaturalPerson();
            }
        } else {
            $regional_managers_list[] = NaturalPersonTable::getInstance()->find($regional_manager_id);
        }

        return $regional_managers_list;
    }
}
