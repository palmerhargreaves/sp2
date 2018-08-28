<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Dialogs', 'doctrine');

/**
 * BaseDialogs
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $header
 * @property clob $description
 * @property date $start
 * @property date $end
 * @property boolean $status
 * @property integer $left_post
 * 
 * @method integer getId()          Returns the current record's "id" value
 * @method string  getHeader()      Returns the current record's "header" value
 * @method clob    getDescription() Returns the current record's "description" value
 * @method date    getStart()       Returns the current record's "start" value
 * @method date    getEnd()         Returns the current record's "end" value
 * @method boolean getStatus()      Returns the current record's "status" value
 * @method integer getLeftPost()    Returns the current record's "left_post" value
 * @method Dialogs setId()          Sets the current record's "id" value
 * @method Dialogs setHeader()      Sets the current record's "header" value
 * @method Dialogs setDescription() Sets the current record's "description" value
 * @method Dialogs setStart()       Sets the current record's "start" value
 * @method Dialogs setEnd()         Sets the current record's "end" value
 * @method Dialogs setStatus()      Sets the current record's "status" value
 * @method Dialogs setLeftPost()    Sets the current record's "left_post" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDialogs extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dialogs');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('header', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));
        $this->hasColumn('description', 'clob', null, array(
             'type' => 'clob',
             'notnull' => false,
             ));
        $this->hasColumn('start', 'date', null, array(
             'type' => 'date',
             'notnull' => false,
             ));
        $this->hasColumn('end', 'date', null, array(
             'type' => 'date',
             'notnull' => false,
             ));
        $this->hasColumn('status', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => false,
             ));
        $this->hasColumn('left_pos', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('top_pos', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('width', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('on_who_just_registered', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false
        ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}