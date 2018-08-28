<?php

/**
 * DealerUser
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class DealerUser extends BaseDealerUser
{
	function getElapsedDaysSummerServiceAction2() 
	{
	    $f = D::getElapsedDays(strtotime(date('d-m-Y')));
		$endDate = $this->getSummerServiceActionEndDate();

	    if(empty($endDate))
	      $l = D::getElapsedDays(strtotime($endDate));

	    if($l > $f)
	      return $l - $f;

	    return 0; 
	}
}