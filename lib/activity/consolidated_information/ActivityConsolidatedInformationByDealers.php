<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 01.11.2018
 * Time: 11:38
 */
class ActivityConsolidatedInformationByDealers
{

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

        //Получаем список региональных менеджеров
        foreach ($this->getRegionalManager() as $manager) {
            if (!array_key_exists($manager->getId(), $manager_dealers_list)) {
                $manager_dealers_list[$manager->getId()] = array(
                    'dealers' => array()
                );
            }

            //Получаем список дилеров
            foreach (DealerTable::getInstance()->createQuery()
                         ->where('status = ?', true)
                         ->andWhereIn('id', $this->_dealers)
                         ->andWhere('(regional_manager_id = ? or nfz_regional_manager_id = ?)', array($manager->getId(), $manager->getId()))
                         ->execute() as $dealer) {

                //Заполняем основную информацию по дилерам
                if (!array_key_exists($dealer->getId(), $manager_dealers_list[$manager->getId()]["dealers"])) {
                    $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()] = array(
                        "dealer" => $dealer->getId(),
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
                    );

                    $budget_calculator = new RealBudgetCalculator($dealer, $this->_year);
                    $dealer_budget_real = $budget_calculator->calculate();
                    $dealer_budget_plan = $budget_calculator->getPlanBudget();

                    //Заполняем для дилера данные по бюджету (план и факт)
                    //Вычисляем общий бюджет, сколько выполнено и % и сколько осталось выполнить
                    $total_plan = 0;
                    $total_fact = 0;
                    for($quarter = 1; $quarter <= 4; $quarter++) {
                        if (isset($dealer_budget_plan[$quarter])) {
                            $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]["budget"]["quarters"][$quarter]["plan"] += $dealer_budget_plan[$quarter];
                            $total_plan += $dealer_budget_plan[$quarter];
                        }

                        if (isset($dealer_budget_real[$quarter])) {
                            $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]["budget"]["quarters"][$quarter]["fact"] += $dealer_budget_real[$quarter];
                            $total_fact += $dealer_budget_real[$quarter];
                        }
                    }

                    //Общие суммы
                    $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]["budget"]["all_year"]["plan"] = $total_plan;
                    $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]["budget"]["all_year"]["fact"] = $total_fact;
                    $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]["budget"]["all_year"]["completed"] = $total_plan != 0 ? $total_fact * 100 / $total_plan : 0;
                    $manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]["budget"]["all_year"]["left_to_complete"] = $total_fact < $total_plan ? $total_plan - $total_fact : $total_plan;

                    var_dump($manager_dealers_list[$manager->getId()]["dealers"][$dealer->getId()]);
                    exit;
                }
            }
        }
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
