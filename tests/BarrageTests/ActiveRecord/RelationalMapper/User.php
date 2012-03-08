<?php
namespace BarrageTests\ActiveRecord\RelationalMapper;

use BarrageTests\ActiveRecord\RelationalMapper\Type;
use BarrageTests\ActiveRecord\RelationalMapper\Status;


class User extends \Barrage\ActiveRecord\RelationalMapper\ActiveRecord
{
    //These static members are required by the ActiveRecord system
    protected static $database = 'ut_barrage';
    protected static $primaryKey = array('id');
    protected static $autoIncrementedField = 'id';

    protected static $table = array
    (
        'name' => 'users',
        'alias' => 'usr'
    );

    protected static $joins = array
    (
        'types' => array
        (
            'alias' => 'typ',
            'on' => "`typ`.`id` = `usr`.`type_id`"
        ),
        'sTaT_useS' => array
        (
            'alias' => 'stt',
            'on' => "`stt`.`ID` = `usr`.`status_id`"
        )
    );

    protected static $fields = array
    (
        'id' => array('name' => 'id'),
        'firstName' => array('name' => 'first_name'),
        'lastName' => array('name' => 'last_name'),
        'username' => array
        (
            'name' => 'username',
            'required' => true
        ),
        'password' => array('name' => 'password'),
        'email' => array('name' => 'email'),
        'typeId' => array('name' => 'type_id'),
        'statusId' => array
        (
            'name' => 'status_id'
        ),
        'status' => array
        (
            'name' => 'title',
            'join_table' => 'sTaT_useS'
        )
    );
    //optional members
    protected static $dataSourceConfiguration = 'active_record';
    //END ActiveRecord memebrs

    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $username;
    public $password;
    public $typeId;
    public $statusId;
    public $status;

    public function __construct($primary_key = null)
    {
        //setup table information

        //setup many to one/one to one relationships
        $this->addObjectReference('typeId', 'BarrageTests\ActiveRecord\RelationalMapper\Type');
        $this->addObjectReference('statusId', 'BarrageTests\ActiveRecord\RelationalMapper\Status');

        parent::__construct($primary_key);
    }

    public function getFields()
    {
        return static::$fields;
    }
}
