<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityFieldsStringField', 'doctrine');

/**
 * BaseActivityFieldsStringField
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityFieldsStringField extends ActivityFields
{
    public function setUp()
    {
        parent::setUp();
        
    }
}