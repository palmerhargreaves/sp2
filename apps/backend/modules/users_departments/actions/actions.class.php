<?php

require_once dirname(__FILE__).'/../lib/users_departmentsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/users_departmentsGeneratorHelper.class.php';

/**
 * users_departments actions.
 *
 * @package    Servicepool2.0
 * @subpackage users_departments
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class users_departmentsActions extends autoUsers_departmentsActions
{
    public function preExecute() {
        /*$departmetns = UsersDepartmentsTable::getInstance()->createQuery()->where('parent_id != ?', 0)->execute();

        foreach ($departmetns as $department) {
            $users_by_department = UserTable::getInstance()->createQuery()->where('post = ?', $department->getName())->execute();

            foreach ($users_by_department as $user) {
                $user->setCompanyDepartment($department->getId());
                $user->save();
            }
        }*/

        parent::preExecute();
    }

    protected function buildQuery() {
        $query = parent::buildQuery();

        return $query->orderBy('parent_id ASC');
    }
}
