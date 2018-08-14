<?php

require_once dirname(__FILE__).'/../lib/main_menu_itemsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/main_menu_itemsGeneratorHelper.class.php';

/**
 * main_menu_items actions.
 *
 * @package    Servicepool2.0
 * @subpackage main_menu_items
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class main_menu_itemsActions extends autoMain_menu_itemsActions
{
    function executeReorder(sfWebRequest $request)
    {
        $data = json_decode($request->getParameter('data'));
        $items = array();

        $ind = 1;
        foreach ($data->{'menus-list'} as $key) {
            if (!empty($key) && is_numeric($key)) {
                $activity = MainMenuItemsTable::getInstance()->find($key);
                if ($activity) {
                    $activity->setPosition($ind);
                    $activity->save();
                }

                $ind++;
            }
        }

        return sfView::NONE;
    }

    protected function buildQuery()
    {
        $query = parent::buildQuery();

        return $query->orderBy('position ASC');
    }

    public function executeLoadRules(sfWebRequest $request) {
        $this->menu_item_id = $request->getParameter('menu_item_id');

        $this->loadRulesData('users_rules');
    }

    public function executeLoadDepartmentsRules(sfWebRequest $request) {
        $this->menu_item_id = $request->getParameter('menu_item_id');

        $this->loadRulesData('users_departments');
    }

    private function loadRulesData($field) {
        $this->menu_item_rules = MainMenuItemsRulesTable::getInstance()->createQuery()->select($field)->where('menu_item_id = ?', $this->menu_item_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
        if (!empty($this->menu_item_rules)) {
            $this->menu_item_rules = explode(":", $this->menu_item_rules[$field]);
        }
    }

    /**
     * Bind menu item rules list
     * @param sfWebRequest $request
     * @return string
     */
    public function executeAccept(sfWebRequest $request) {
        $menu_item_id = $request->getParameter('menu_item_id');
        $menu_item_rules_list = $request->getParameter('rules_list');
        $task = $request->getParameter('task');

        $task = implode('', array_map(function($item) {
            return ucfirst($item);
        }, explode('_', $task)));

        $menu_item_rule = MainMenuItemsRulesTable::getInstance()->createQuery()->where('menu_item_id = ?', $menu_item_id)->fetchOne();
        if (!$menu_item_rule) {
            $menu_item_rule = new MainMenuItemsRules();
            $menu_item_rule->setMenuItemId($menu_item_id);
        }

        $task_func = "set".$task;
        $menu_item_rule->$task_func(implode(':', $menu_item_rules_list));
        $menu_item_rule->save();

        return sfView::NONE;
    }

    public function executeSaveDealerTypes(sfWebRequest $request) {
        $menu_item_id = $request->getParameter('menu_item_id');
        $params = $request->getParameter('items');

        $rules = array();
        foreach ($params as $key => $param) {
            if ($param['value'] == 1) {
                $result[] = $param['dealer_rule'];
            }
        }

        $menu_item = MainMenuItemsRulesTable::getInstance()->createQuery()->where('menu_item_id = ?', $menu_item_id)->fetchOne();
        if (!$menu_item) {
            $menu_item = new MainMenuItemsRules();
            $menu_item->setMenuItemId($menu_item_id);
        }

        $menu_item->setDealersTypes(implode(':', $result));
        $menu_item->save();

        return sfView::NONE;
    }
}
