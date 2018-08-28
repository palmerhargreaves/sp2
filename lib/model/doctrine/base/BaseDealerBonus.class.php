<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('DealerBonus', 'doctrine');

/**
 * BaseDealerBonus
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $year
 * @property integer $quarter
 * @property boolean $bonus
 * @property string $comment
 * @property Dealer $Dealer
 * 
 * @method integer     getId()        Returns the current record's "id" value
 * @method integer     getDealerId()  Returns the current record's "dealer_id" value
 * @method integer     getYear()      Returns the current record's "year" value
 * @method integer     getQuarter()   Returns the current record's "quarter" value
 * @method boolean     getBonus()     Returns the current record's "bonus" value
 * @method string      getComment()   Returns the current record's "comment" value
 * @method Dealer      getDealer()    Returns the current record's "Dealer" value
 * @method DealerBonus setId()        Sets the current record's "id" value
 * @method DealerBonus setDealerId()  Sets the current record's "dealer_id" value
 * @method DealerBonus setYear()      Sets the current record's "year" value
 * @method DealerBonus setQuarter()   Sets the current record's "quarter" value
 * @method DealerBonus setBonus()     Sets the current record's "bonus" value
 * @method DealerBonus setComment()   Sets the current record's "comment" value
 * @method DealerBonus setDealer()    Sets the current record's "Dealer" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealerBonus extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealer_bonus');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('year', 'integer', 2, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => 2,
             ));
        $this->hasColumn('quarter', 'integer', 1, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => 1,
             ));
        $this->hasColumn('bonus', 'boolean', null, array(
             'type' => 'boolean',
             'default' => true,
             'notnull' => true,
             ));
        $this->hasColumn('comment', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));


        $this->index('year', array(
             'fields' => 
             array(
              0 => 'year',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Dealer', array(
             'local' => 'dealer_id',
             'foreign' => 'id'));
    }
}