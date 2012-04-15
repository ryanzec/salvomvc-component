<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\ActiveRecord\RelationalMapper;

use Salvo\Barrage\DataSource\Relational\Driver\Mysql;
use Salvo\Barrage\DataSource\Relational\IDataSource;
use Salvo\Barrage\DataSource\Relational\DataSourceFactory;
use Salvo\Barrage\Configuration;
use Salvo\IArrayable;

/**
 * Base active record object
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
abstract class ActiveRecord implements IArrayable
{
    /**
     * STRUCTURE
     * 'class member name' => array
     * (
     *     'name' => string (required)
     *     'join_table' => string (required if this field is pulled for a table that is joined in)
     *     'save_type' => string (required if this field is pulled for a table that is joined in)
     * )
     */
    //protected static $fields = array();

    /**
     * STRUCTURE
     * array
     * (
     *     'name' => string (required)
     *     'alias' => string (required)
     * )
     */
    //protected static $table = array();

    /**
     * STRUCTURE
     * array
     * (
     *     'name' => string (required)
     *     'alias' => string (required)
     *     'type' => string (optional, either, inner or left)
     *     'on' => string (required)
     *     'database' => string (optional, defaults to main database)
     * )
     */
    //protected static $joins = array();

    /**
     * @var string
     */
    //protected static $database = null;

    /**
     * @var array
     */
    //protected static $primaryKey = array();

    /**
     * @var null|string
     */
    //protected static $autoIncrementedField = null;

    /**
     * @var array
     */
    private $referenceObjects = array();

    /**
     * @var string
     */
    protected static $dataSourceConfiguration = 'default';

    /**
     * @var IDataSource
     */
    protected $dataSource = null;

    /**
     * @var string
     */
    private $dataSourceStatus = 'new';

    /**
     * @var array
     */
    private static $validDataSourceStatuses = array('new', 'loaded');

    /**
     * Name of members to skip when saving a record
     *
     * Useful for fields that are updated automatically by the data source (like a ON UPDATE for a timestamp field in MySQL)
     *
     * @var array
     */
    protected static $skipSaveMembers = array();

    /**
     * Constructor with allows for loading object by primary key when loading object
     *
     * @param null|string|array $primaryKey optional The value(s) of a primary key to load object as
     */
    public function __construct($primaryKey = null)
    {
        $this->dataSource = self::getDataSourceInstance();

        if(!empty($primaryKey))
        {
            if(is_array($primaryKey))
            {
                foreach(static::$primaryKey as $keyPart)
                {
                    if(!isset($primaryKey[$keyPart]))
                    {
                        throw new ActiveRecordException("Missing value for '{$keyPart}' with loading active record object by primary key");
                    }

                    $this->$keyPart = $primaryKey[$keyPart];
                }
            }
            else if(count(static::$primaryKey) === 1)
            {
                $member = static::$primaryKey[0];
                $this->$member = $primaryKey;
            }
            else
            {
                throw new ActiveRecordException('Primary key has more than 1 field but only 1 value given for primary key');
            }

            $this->loadByPrimaryKey();

            if($this->isNew())
            {
                $this->reset();
            }
        }
    }

    public function diff(ActiveRecord $object)
    {
        //make sure the passed object is the same of the class calling it
        if(get_called_class() !== get_class($object))
        {
            throw new \Exception("Can't diff two different object types");
        }

        $diff = array();

        foreach(static::$fields as $member => $options)
        {
            if($this->$member != $object->$member)
            {
                $diff[$member] = array
                (
                    'selfValue' => $this->$member,
                    'passedValue' => $object->$member
                );
            }
        }

        return $diff;
    }

    /**
     * Adds a reference object
     *
     * @param $foreignMember
     * @param $objectName
     */
    public function addObjectReference($foreignMember, $objectName)
    {
        $this->referenceObjects[$foreignMember] = $objectName;
    }

    /**
     * Retrieves a reference object
     *
     * @param $foreignMember
     * @return null
     * @throws ActiveRecordException
     */
    public function getReferenceObjects($foreignMember, $namespace, $className = null, $foreignMemberLink = null)
    {
        if((empty($className) && empty($foreignMemberLink)) && empty($this->referenceObjects[$foreignMember]) || empty(static::$fields[$foreignMember]))
        {
            if(empty($this->referenceObjects[$foreignMember]))
            {
                throw new ActiveRecordException("There is no configured reference object for {$foreignMember}");
            }
            else
            {
                throw new ActiveRecordException("There is no configured for field {$foreignMember}");
            }
        }

        if(empty($className))
        {
            $className = $this->referenceObjects[$foreignMember];
        }

        $className = $namespace . '\\' . $className;

        if(!class_exists($className))
        {
            throw new ActiveRecordException("{$foreignMember} is linked to class {$className} but class {$className} does not exist");
        }

        if(empty($this->$foreignMember))
        {
            return null;
        }

        if(empty($foreignMemberLink))
        {
            return new $className($this->$foreignMember);
        }
        else
        {
            $data = array($foreignMemberLink => $this->$foreignMember);
            return $className::getOne($data);
        }
    }

    /**
     * Return the status of this object for the data source
     *
     * @return string
     */
    public function getDataSourceStatus()
    {
        $this->validateDataSourceStatus();
        return $this->dataSourceStatus;
    }

    public static function getMemberValues($member)
    {
        if(!empty(static::$fields[$member]['values']))
        {
            return static::$fields[$member]['values'];
        }

        return false;
    }

    /**
     * Converts the object to an array with only the data stored in the data source (mapping information not returned)
     *
     * @return array The data array
     */
    public function toArray()
    {
        $data = array();

        foreach(static::$fields as $member => $options)
        {
            $data[$member] = $this->$member;
        }

        return $data;
    }

    /**
     * Save the object to the database
     *
     * @return void
     */
    public function save()
    {
        $this->validateDataSourceStatus();

        switch($this->dataSourceStatus)
        {
            case 'new':
                $this->insert();
                break;

            case 'loaded':
                $this->update();
                break;

            default:
                throw new ActiveRecordException("Invalid data source for resetting : {$this->dataSourceStatus}");
                break;
        }
    }

    /**
     * Delete the record from the database
     *
     * @return void
     *
     * @throws ActiveRecordException
     */
    public function delete()
    {
        $where = $this->buildPrimaryKeyWhere();
        $this->dataSource->delete(static::$table['name'], $where, static::$database);
        $this->clear();
    }

    /**
     * Resets the object that is stored in some backend system back to the state that is stored.
     *
     * @throws ActiveRecordException
     * @return void
     */
    public function reset()
    {
        $this->validateDataSourceStatus();

        switch($this->dataSourceStatus)
        {
            case 'new':
                $this->clear();
                break;

            case 'loaded':
                $this->loadByPrimaryKey();
                break;

            default:
                throw new ActiveRecordException("Invalid data source for resetting : {$this->dataSourceStatus}");
                break;
        }
    }

    /**
     * A static method for simple active record querying
     *
     * @static
     *
     * @param array $where
     * @param array $joins
     * @param array $group
     * @param array $order
     * @param null $limit
     * @param int $offset
     *
     * @return ActiveRecordCollection Collection of active record objects
     */
    public static function get($where = array(), $joins = array(), $group = array(), $order = array(), $limit = null, $offset = 0)
    {
        $className = get_called_class();
        $dataSource = self::getDataSourceInstance();
        $select = self::getSelectArray();
        $from = self::getFromArray();

        if(!empty($joins))
        {
            foreach($joins as $tableName => $options)
            {
                if(!isset(static::$joins[$tableName]))
                {
                    static::$joins[$tableName] = $options;
                }
            }
        }

        $where = self::getWhereArray($where);
        $sql = $dataSource->simpleSelectBuilder($select, $from, static::$joins, $where, $group, $order, $limit, $offset);

        $dataSet = $dataSource->getAll($sql);
        $objects = array();

        foreach($dataSet as $row)
        {
            $object = new $className();
            $object->loadByArray($row, true);
            $objects[] = $object;
        }

        return new ActiveRecordCollection($objects);
    }

    public static function getOne($where = array(), $joins = array(), $group = array(), $order = array())
    {
        $collection = static::get($where, $joins, $group, $order, 1);
        return ($collection->count() == 1) ? $collection->seek(0) : null;
    }

    /**
     * A static method for simple active record querying count
     *
     * @static
     *
     * @param array $where
     * @param array $group
     *
     * @return ActiveRecordCollection
     */
    public static function getCount($where = array(), $group = array())
    {
        $dataSource = self::getDataSourceInstance();
        $from = self::getFromArray();

        $where = self::getWhereArray($where);
        $sql = $dataSource->simpleSelectBuilder(array('COUNT(*) as count' => $from['name']), $from, static::$joins, $where, $group);

        return $dataSource->getOne($sql);
    }

    /**
     * Pulls data from the relation database and load it into the current object
     *
     * @return void
     */
    private function loadByPrimaryKey()
    {
        $select = self::getSelectArray();
        $from = self::getFromArray();

        $where = array();

        foreach(static::$primaryKey as $primaryKeyPart)
        {
            $where[$primaryKeyPart] = $this->$primaryKeyPart;
        }

        $sql = $this->dataSource->simpleSelectBuilder($select, $from, static::$joins, $where);
        $row = $this->dataSource->getRow($sql);
        $this->loadByArray($row, true);
    }

    /**
     * Creates the from array needed to build certain queries
     *
     * @static
     *
     * @return array
     */
    private static function getFromArray()
    {
        $from = static::$table;
        $from['database'] = static::$database;
        return $from;
    }

    /**
     * Return the select array needed to build certain queries
     *
     * @static
     *
     * @return array
     */
    private static function getSelectArray()
    {
        self::validateFieldsAreSet();

        $select = array();

        foreach(static::$fields as $member => $options)
        {
            $name = static::getQualifiedSqlField($member);

            $select[$name] = (empty($options['join_table'])) ? static::$table['name'] : $options['join_table'];
        }

        return $select;
    }

    private static function getWhereArray($where)
    {
        $newArray = array();

        foreach($where as $member => $condition)
        {
            $fieldName = self::getQualifiedSqlField($member);
            $newArray[$fieldName] = $condition;
        }

        return $newArray;
    }

    private static function getQualifiedSqlField($member)
    {
        //if it is not a member, assume it is already a fully qualified sql field
        if(empty(static::$fields[$member]))
        {
            return $member;
        }

        if(!empty(static::$fields[$member]['join_table']))
        {
            $alias = (!empty(static::$joins[static::$fields[$member]['join_table']]['alias']))
            ? static::$joins[static::$fields[$member]['join_table']]['alias']
            : static::$fields[$member]['join_table'];
        }
        else
        {
            $alias = (!empty(static::$table['alias'])) ? static::$table['alias'] : static::$table['name'];
        }

        return $alias . '.' . static::$fields[$member]['name'];
    }

    /**
     * Load an active record by an array
     *
     * @param $data
     * @param bool $fromDataSource
     * @param bool $urlDecode
     */
    public function loadByArray($data, $fromDataSource = false, $urlDecode = false)
    {
        if(!empty($data))
        {
            foreach(static::$fields as $member => $options)
            {
                $dataKey = $this->getFieldNameFromFieldString($options['name']);
                if(array_key_exists($dataKey, $data))
                {
                    $this->$member = ($urlDecode) ? urldecode($data[$dataKey]) : $data[$dataKey];
                }
            }

            if($fromDataSource)
            {
                $this->dataSourceStatus = 'loaded';
            }
        }
    }

    public static function resolvePrimaryKey($member, $value)
    {
        $where = static::getWhereArray(array($member => $value));
        $data = static::get($where);
        $dataCount = count($data);
        $primaryKeyFields = static::$primaryKey;
        $primaryKey = array();

        switch($dataCount)
        {
            case 0:
                $primaryKey = array();
                break;

            case 1:
                $object = $data->seek(0);

                foreach($primaryKeyFields as $member)
                {
                    $primaryKey = array
                    (
                        $member => $object->$member
                    );
                }
                break;

            default:
                if($dataCount > 1)
                {
                    throw new \Exception("Can't resolve when passed data returns more than 1 record");
                }
                else
                {
                    throw new \Exception("Unknown error resolving primary key");
                }
                break;
        }

        return $primaryKey;
    }

    /**
     * Helper function to determine when persisting, if we should insert or update
     *
     * @return bool Whether this record is new
     */
    public function isNew()
    {
        return $this->dataSourceStatus == 'new';
    }

    /**
     * Helper function for inserting data from this object into the database
     *
     * @return void
     */
    private function insert()
    {
        $insertMembers = $this->getDataArrayForQueryBuilder('insert');
        $newId = $this->dataSource->insert(static::$table['name'], $insertMembers, static::$database);
        $this->dataSourceStatus = 'loaded';

        if(!empty($newId) && count(static::$primaryKey) === 1)
        {
            $member = static::$primaryKey[0];
            $this->$member = $newId;
        }

        $this->reset();
    }

    /**
     * Helper function for updating data from the object into the database
     *
     * @return void
     */
    private function update()
    {
        $where = $this->buildPrimaryKeyWhere();
        $updateMembers = $this->getDataArrayForQueryBuilder('update');
        $this->dataSource->update(static::$table['name'], $updateMembers, $where, static::$database);
        $this->reset();
    }

    /**
     * Helper parse through the data in the
     *
     * @param $queryType
     *
     * @return array
     *
     * @throws ActiveRecordException
     */
    private function getDataArrayForQueryBuilder($queryType)
    {
        if($queryType != 'insert' && $queryType != 'update')
        {
            throw new ActiveRecordException("Query type must either be 'insert' or 'update', '{$queryType}' given");
        }

        $members = array();
        $saveTypes = array('both', $queryType);

        self::validateFieldsAreSet();

        foreach(static::$fields as $member => $fieldOptions)
        {
            if
            (
                (
                    (empty($fieldOptions['join_table']) || $fieldOptions['join_table'] == static::$table['name'])
                )
                &&
                (
                    empty($fieldOptions['save_type'])
                    ||
                    in_array($fieldOptions['save_type'], $saveTypes)
                )
                &&
                !in_array($member, static::$skipSaveMembers)
            )
            {
                $members[$fieldOptions['name']] = $this->$member;
            }
        }

        return $members;
    }

    /**
     * Makes sure there is at least one field configured for the object
     *
     * @throws ActiveRecordException
     */
    private static function validateFieldsAreSet()
    {
        if(empty(static::$fields))
        {
            throw new ActiveRecordException('No fields are set for ' . get_called_class() . '.');
        }
    }

    /**
     * Checks to see if the data member exists
     *
     * @param $memberName
     *
     * @return bool
     */
    private function dataMemberExists($memberName)
    {
        return isset(static::$fields[$memberName]);
    }

    /**
     * Validates the data source status is a valid one
     *
     * @throws ActiveRecordException
     */
    private function validateDataSourceStatus()
    {
        if(!in_array($this->dataSourceStatus, self::$validDataSourceStatuses))
        {
            throw new ActiveRecordException("Record has invalid data source status of : {$this->dataSourceStatus}");
        }
    }

    /**
     * Whether or not all the fields for the primary key are set.
     *
     * @throws ActiveRecordException
     * @return bool
     */
    private function isPrimaryKeySet()
    {
        if(is_array(static::$primaryKey) && !empty(static::$primaryKey))
        {
            foreach(static::$primaryKey as $keyPart)
            {
                if(empty($this->$keyPart))
                {
                    break;
                }
            }

            $isPrimaryKeySet = true;
        }
        else
        {
            throw new ActiveRecordException("Primary key must be an array");
        }

        return $isPrimaryKeySet;
    }

    /**
     * Builds a where statement based on the primary key
     *
     * @return null|string
     *
     * @throws ActiveRecordException
     */
    private function buildPrimaryKeyWhere()
    {
        if(!$this->isPrimaryKeySet())
        {
            throw new ActiveRecordException("Can't build primary key where when all primary key members are not set");
        }

        $where = null;

        foreach(static::$primaryKey as $primaryKeyPart)
        {
            if(!empty($where))
            {
                $where .= ' AND ';
            }

            $field = static::$fields[$primaryKeyPart]['name'];
            $where .= "`{$field}` = '{$this->$primaryKeyPart}'";
        }

        return $where;
    }

    /**
     * Sets all values for sql field related members to null
     *
     * @return void
     */
    private function clear()
    {
        foreach(static::$fields as $member => $options)
        {
            $this->$member = null;
        }
    }
    /**
     * Returns an instance of a relational data source
     *
     * @static
     * @return Salvo\Barrage\DataSource\Relational\IDataSource
     */
    protected static function getDataSourceInstance()
    {
        return DataSourceFactory::buildFromConfiguration(static::$dataSourceConfiguration);
    }

    private function getFieldNameFromFieldString($field)
    {
        if(stripos($field, ' AS ') !== false)
        {
            $explodeValue = (strpos($field, ' AS ') !== false) ? ' AS ' : ' as ';
            $fieldParts = explode($explodeValue, $field, 2);
            return $fieldParts[1];
        }

        return $field;
    }
}
