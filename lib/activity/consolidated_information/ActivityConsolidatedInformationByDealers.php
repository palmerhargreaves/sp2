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

        $active_activities_list = ActivityTable::getInstance()->createQuery()->select('id, name')->where('finished = ?', false)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

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
                                    'year' => $this->_year
                                )
                            )
                        );

                        $completed_models = $completed_models_factory->getModelsList();
                        foreach ($completed_models as $model) {
                            var_dump($model['model']->getId());
                        }
                        exit;
                    }
                }
            }
        }

        return $manager_dealers_list;
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
                         ->where('g.id = ?', User::USER_GROUP_REGIONAL_MANAGER)
                         ->orderBy('u.name ASC')
                         ->execute() as $manager) {
                $regional_managers_list[] = $manager->getNaturalPerson();
            }
        } else {
            $regional_managers_list[] = NaturalPersonTable::getInstance()->find($regional_manager_id);
        }

        return $regional_managers_list;
    }
}
