<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('DealerDiscussion', 'doctrine');

/**
 * BaseDealerDiscussion
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $discussion_id
 * @property Dealer $Dealer
 * @property Discussion $Discussion
 * 
 * @method integer          getId()            Returns the current record's "id" value
 * @method integer          getDealerId()      Returns the current record's "dealer_id" value
 * @method integer          getDiscussionId()  Returns the current record's "discussion_id" value
 * @method Dealer           getDealer()        Returns the current record's "Dealer" value
 * @method Discussion       getDiscussion()    Returns the current record's "Discussion" value
 * @method DealerDiscussion setId()            Sets the current record's "id" value
 * @method DealerDiscussion setDealerId()      Sets the current record's "dealer_id" value
 * @method DealerDiscussion setDiscussionId()  Sets the current record's "discussion_id" value
 * @method DealerDiscussion setDealer()        Sets the current record's "Dealer" value
 * @method DealerDiscussion setDiscussion()    Sets the current record's "Discussion" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealerDiscussion extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealer_discussion');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('discussion_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
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

        $this->hasOne('Discussion', array(
             'local' => 'dealer_id',
             'foreign' => 'id'));
    }
}