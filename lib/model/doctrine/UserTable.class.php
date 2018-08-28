<?php

/**
 * UserTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class UserTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return UserTable
   */
  static function getInstance()
  {
    return Doctrine_Core::getTable('User');
  }
  
  function isUserActive($email)
  {
    return $this->createQuery()
                ->where('active=? and email=?', array(true, $email))
                ->count() > 0;
  }
  
  function callWithImporter(Closure $callback)
  {
    $importers = UserTable::getInstance()
                 ->createQuery('u')
                 ->innerJoin('u.Group g')
                 ->innerJoin('g.Roles r WITH r.role=?', 'importer')
                 ->where('u.active=?', true)
                 ->execute();
    
    foreach($importers as $importer)
      $callback($importer);
  }
  
  function callWithAdministrator(Closure $callback)
  {
    $admins = UserTable::getInstance()
              ->createQuery('u')
              ->innerJoin('u.Group g')
              ->innerJoin('g.Roles r WITH r.role=?', 'admin')
              ->where('u.active=?', true)
              ->execute();
    
    foreach($admins as $admin)
      $callback($admin);
  }

   function callWithDealer(Closure $callback) {
      $dealers = UserTable::getInstance()
                    ->createQuery('u')
                    ->innerJoin('u.Group g')
                    ->innerJoin('g.Roles r WITH r.role=?', 'dealer')
                      ->where('u.active=?', true)
                      //->limit(1)
                      ->execute();

      foreach($dealers as $dealer)
        $callback($dealer);
  }
  
  
  static function getUsersWithSpecialBudget($status = 1) {
	 return UserTable::getInstance()
						->createQuery('u')
							->where('u.special_budget_status = ?', $status)
							->andWhere('u.active = ?', true)
						->execute();
  }

  static function getUsersWithSummerServiceAction() {
      return UserTable::getInstance()
            ->createQuery('u')
              ->where('u.summer_action_start_date != ?', '')
            ->execute();
  }

  static function getUsersWithSummerServiceAction2() {
      return DealerUserServiceActionTable::getInstance()
            ->createQuery('u')
              ->where('u.summer_service_action_start_date != ?', '')
            ->execute();
  }

  static function getUsersWithWinterServiceAction() {
      return DealerWinterServiceActionTable::getInstance()
            ->createQuery('u')
              ->where('u.start_date != ?', '')
            ->execute(); 
  }

  static function getUsersWithSpringServiceAction() {
      return DealerSpringServiceActionTable::getInstance()
            ->createQuery('u')
              ->where('u.start_date != ?', '')
            ->execute(); 
  }

  static function getUsersWithProdOfYear3() {
      return DealerUserProdOfYear3Table::getInstance()
            ->createQuery('u')
            ->select()
            ->execute();
  }
}