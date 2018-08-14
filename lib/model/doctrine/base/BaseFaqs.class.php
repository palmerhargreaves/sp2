<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Faqs', 'doctrine');

/**
 * BaseFaqs
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property clob $question
 * @property clob $answer
 * @property string $image
 * @property interger $user_id
 * @property integer $status
 * @property User $User
 * 
 * @method integer  getId()       Returns the current record's "id" value
 * @method clob     getQuestion() Returns the current record's "question" value
 * @method clob     getAnswer()   Returns the current record's "answer" value
 * @method string   getImage()    Returns the current record's "image" value
 * @method interger getUserId()   Returns the current record's "user_id" value
 * @method integer  getStatus()   Returns the current record's "status" value
 * @method User     getUser()     Returns the current record's "User" value
 * @method Faqs     setId()       Sets the current record's "id" value
 * @method Faqs     setQuestion() Sets the current record's "question" value
 * @method Faqs     setAnswer()   Sets the current record's "answer" value
 * @method Faqs     setImage()    Sets the current record's "image" value
 * @method Faqs     setUserId()   Sets the current record's "user_id" value
 * @method Faqs     setStatus()   Sets the current record's "status" value
 * @method Faqs     setUser()     Sets the current record's "User" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseFaqs extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('faqs');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('question', 'clob', null, array(
             'type' => 'clob',
             'notnull' => true,
             ));
        $this->hasColumn('answer', 'clob', null, array(
             'type' => 'clob',
             'notnull' => true,
             ));
        $this->hasColumn('image', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 255,
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'interger',
             'notnull' => true,
             ));
        $this->hasColumn('position', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 0
        ));
        $this->hasColumn('status', 'boolean', null, array(
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
        $this->hasOne('User', array(
             'local' => 'user_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}