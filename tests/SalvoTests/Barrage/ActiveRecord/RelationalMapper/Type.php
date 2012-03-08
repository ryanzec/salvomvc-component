<?php
namespace SalvoTests\Barrage\ActiveRecord\RelationalMapper;

class Type extends \Salvo\Barrage\ActiveRecord\RelationalMapper\ActiveRecord
{
    //These static members are required by the ActiveRecord system
    protected static $database = 'ut_barrage';
    protected static $primaryKey = array('id');
    protected static $autoIncrementedField = 'id';

    protected static $table = array
    (
        'name' => 'types',
        'alias' => 'type'
    );

    protected static $joins = array();

    protected static $fields = array
    (
        'id' => array('name' => 'id'),
        'title' => array('name' => 'title'),
        'global' => array('name' => 'global')
    );
    //optional members
    protected static $dataSourceConfiguration = 'active_record';
    //END ActiveRecord members

    public $id;
    public $title;
    public $global;

    public function __construct($primary_key = null)
    {

        parent::__construct($primary_key);
    }

    public function getFields()
    {
        return static::$fields;
    }
}
