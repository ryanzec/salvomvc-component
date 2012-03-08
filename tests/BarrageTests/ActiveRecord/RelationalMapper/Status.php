<?php
namespace BarrageTests\ActiveRecord\RelationalMapper;

class Status extends \Barrage\ActiveRecord\RelationalMapper\ActiveRecord
{
    //These static members are required by the ActiveRecord system
    protected static $database = 'ut_barrage';
    protected static $primaryKey = array('id');
    protected static $autoIncrementedField = 'id';

    protected static $table = array
    (
        'name' => 'sTaT_useS',
        'alias' => 'stt'
    );

    protected static $joins = array();

    protected static $fields = array
    (
        'id' => array('name' => 'ID'),
        'title' => array('name' => 'tItLe'),
        'global' => array('name' => 'GlObAl')
    );
    //optional members
    protected static $dataSourceConfiguration = 'active_record';
    //END ActiveRecord memebrs

    protected $id;
    protected $title;
    protected $global;

    public function __construct($primary_key = null)
    {
        parent::__construct($primary_key);
    }

    public function getFields()
    {
        return static::$fields;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getGlobal()
    {
        return $this->global;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function setGlobal($value)
    {
        $this->global = $value;
    }
}
