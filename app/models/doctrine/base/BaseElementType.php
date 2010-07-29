<?php

/**
 * BaseElementType
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property string $class
 * @property Doctrine_Collection $Element
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BaseElementType extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('element_type');
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'unique' => true,
             'length' => '255',
             ));
        $this->hasColumn('class', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'unique' => true,
             'length' => '255',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Element', array(
             'local' => 'id',
             'foreign' => 'elementType'));
    }
}