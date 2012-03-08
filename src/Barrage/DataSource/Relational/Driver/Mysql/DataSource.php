<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\DataSource\Relational\Driver\Mysql;

use Barrage\DataSource\Relational\BaseDataSource;
use Barrage\DataSource\Relational\Exception\RelationalSqlException;

/**
 * Object from make sql calls to a MySQL database
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class DataSource extends BaseDataSource
{
    /**
     * Generates a sql call for selecting data
     * NOTE: This is designed to be used with the active record system and while this can build relatively complex queries it is recommended you build
     * very complex queries manually
     *
     * @param array $select The fields to select
     * @param array $from The main table
     * @param array $join optional Join tables
     * @param array $where optional Where condition
     * @param array $group
     * @param array $order
     * @param null $limit
     * @param int $offset
     *
     * @return string Generated sql
     */
    function simpleSelectBuilder($select, $from, $join = array(), $where = array(), $group = array(), $order = array(), $limit = null, $offset = 0)
    {
        //select
        $selectStatement = null;

        foreach($select as $field => $table)
        {
            $fieldAlias = null;

            if(stripos($field, ' AS ') !== false)
            {
                $explodeValue = (strpos($field, ' AS ') !== false) ? ' AS ' : ' as ';
                $fieldParts = explode($explodeValue, $field, 2);
                $field = $fieldParts[0];
                $fieldAlias = $fieldParts[1];
            }

            $tableAlias = $this->getTableAlias($table, $from, $join);

            if(!empty($selectStatement))
            {
                $selectStatement .= ', ';
            }

            if(empty($fieldAlias))
            {
                $selectStatement .= "`{$tableAlias}`.`{$field}`";
            }
            else
            {
                $selectStatement .= "{$field} AS `{$fieldAlias}`";
            }
        }

        //from
        if(empty($from) || empty($from['name']) || empty($from['database']))
        {
            throw new RelationalSqlException("From configuration miss either database ({$from['name']}) or table name ({$from['database']})");
        }

        $defaultDatabase = $from['database'];
        $fromAlias = $this->getTableAlias($from['name'], $from, $join);
        $fromStatement = "`{$defaultDatabase}`.`{$from['name']}` AS `{$fromAlias}`";

        //join
        $joinStatement = null;

        if(!empty($join))
        {
            foreach($join as $table => $options)
            {
                if(empty($options['on']))
                {
                    throw new RelationalSqlException("Join configuration missing on clause");
                }

                $tableAlias = $this->getTableAlias($table, $from, $join);
                $database = (empty($options['database'])) ? $defaultDatabase : $options['database'];
                $type = (!empty($options['type'])) ? $options['type'] : 'inner';

                $joinStatement .= " " . strtoupper($type) . " JOIN `{$database}`.`{$table}` AS `{$tableAlias}` ON {$options['on']}";
            }
        }

        //where
        $whereStatement = null;

        if(!empty($where))
        {
            foreach($where as $key => $options)
            {
                $conditionSegment = '(';

                if(is_numeric($key))
                {
                    foreach($options as $whereLogic => $options2)
                    {
                        $this->validateWhereLogic($whereLogic);

                        foreach($options2 as $innerKey => $innerOptions)
                        {
                            $condition = $this->getWhereCondition($innerKey, $innerOptions, $fromAlias);

                            if($conditionSegment != '(')
                            {
                                $conditionSegment .= ' ' . strtoupper($whereLogic) . ' ';
                            }

                            $conditionSegment .= $condition;
                        }
                    }
                }
                else
                {
                    $conditionSegment .= $this->getWhereCondition($key, $options, $fromAlias);
                }

                $conditionSegment .= ')';

                if(!empty($whereStatement))
                {
                    $whereStatement .= " AND ";
                }

                $whereStatement .= $conditionSegment;
            }
        }

        //group by
        $groupStatement = null;

        if(!empty($group))
        {
            foreach($group as $key => $value)
            {
                if(!empty($groupStatement))
                {
                    $groupStatement.= ", ";
                }

                if(is_numeric($key))
                {
                    $alias = $fromAlias;
                    $key = $value;
                }
                else if(is_string($value))
                {
                    $alias = $this->getTableAlias($value, $from, $join);
                }
                else
                {
                    throw new RelationalSqlException("Invalid value passed for group item {$key}");
                }

                $groupStatement .= "`{$alias}`.`{$key}`";
            }

            $groupStatement = " GROUP BY {$groupStatement}";
        }

        //order by
        $orderStatement = null;

        if(!empty($order))
        {
            foreach($order as $key => $value)
            {
                if(!empty($orderStatement))
                {
                    $orderStatement.= ", ";
                }

                if(is_numeric($key))
                {
                    $alias = $fromAlias;
                    $key = $value;
                }
                else if(is_string($value))
                {
                    $alias = $this->getTableAlias($value, $from, $join);
                }
                else
                {
                    throw new RelationalSqlException("Invalid value passed for group item {$key}");
                }

                $parsedValue = $this->parseOrderValue($key);
                $key = $parsedValue['key'];
                $sort = $parsedValue['sort'];

                $orderStatement .= "`{$alias}`.`{$key}` " . strtoupper($sort);
            }

            $orderStatement = " ORDER BY {$orderStatement}";
        }

        //limit
        $limitStatement = null;

        if(!empty($limit))
        {
            $limitStatement = " LIMIT {$offset}, {$limit}";
        }

        $sql = "SELECT {$selectStatement} FROM {$fromStatement}";

        if(!empty($joinStatement))
        {
            $sql .= "{$joinStatement}";
        }

        if(!empty($whereStatement))
        {
            $sql .= " WHERE {$whereStatement}";
        }

        if(!empty($groupStatement))
        {
            $sql .= "{$groupStatement}";
        }

        if(!empty($orderStatement))
        {
            $sql .= "{$orderStatement}";
        }

        if(!empty($limitStatement))
        {
            $sql .= "{$limitStatement}";
        }

        return $sql;
    }

    /**
     * Generates a sql call for inserting data
     *
     * @abstract
     * @param string $table The table to insert into
     * @param mixed[] $data The data to insert
     * @param null|string $database optional The database the table is in (default to connection's default database)
     *
     * @return string Generated sql
     */
    function simpleInsertBuilder($table, $data, $database = null)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;
        $fields = null;
        $values = null;

        foreach($data as $field => $value)
        {
            $value = $this->cleanQuote($value);
            $fields .= (empty($fields)) ? "`{$field}`" : ", `{$field}`";
            $values .= (empty($values)) ? "{$value}" : ", {$value}";
        }

        return "INSERT INTO `{$database}`.`{$table}`({$fields}) VALUES({$values})";
    }

    /**
     * Generates a sql call for updating data
     *
     * @abstract
     * @param string $table The table to update
     * @param mixed[] $data The updated data
     * @param string $where The where part of the statement that comes after WHERE in the sql call
     * @param null|string $database optional The database the table is in (default to connection's default database)
     *
     * @return string Generated sql
     */
    function simpleUpdateBuilder($table, $data, $where, $database = null)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;
        $set = null;

        foreach($data as $field => $value)
        {
            $value = $this->cleanQuote($value);
            $set .= (empty($set)) ? "`{$field}` = {$value}" : ", `{$field}` = {$value}";
        }

        return "UPDATE `{$database}`.`{$table}` SET {$set} WHERE {$where}";
    }

    /**
     * Return the name of the database vendor in all lowercase
     *
     * @abstract
     *
     * @return string The name of the database vendor
     */
    public function getServerVendorName()
    {
        return 'mysql';
    }

    /**
     * Deletes records from a table
     *
     * @abstract
     * @param string $table The table to delete record from
     * @param string $where The where statement
     * @param null|string $database optional The database of the table
     *
     * @return void
     */
    public function delete($table, $where, $database = null)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;
        $sql = "DELETE FROM {$database}.{$table}
                WHERE {$where}";

        $this->query($sql);
    }

    /**
     * Returns a tables alias
     *
     * @param $table
     * @param $from
     * @param array $join
     *
     * @return mixed
     *
     * @throws \Barrage\DataSource\Relational\Exception\RelationalSqlException
     */
    private function getTableAlias($table, $from, $join = array())
    {
        if((empty($join) || empty($join[$table])) && $table != $from['name'])
        {
            throw new RelationalSqlException("Unable to find alias for table {$table}");
        }

        if(!empty($join[$table]))
        {
            $alias = (!empty($join[$table]['alias'])) ? $join[$table]['alias'] : $table;
        }
        else
        {
            $alias =  (!empty($from['alias'])) ? $from['alias'] : $table;
        }

        return $alias;
    }

    /**
     * Converts an array into a cleaned string that can be use in sql (with IN or NOT IN for example)
     *
     * @param $array
     *
     * @return null|string
     */
    private function arrayToCleanStringSql($array)
    {
        $cleanedSqlString = null;

        if(is_array($array))
        {
            $cleanedSqlString = null;

            foreach($array as $value)
            {
                if(!empty($cleanedSqlString))
                {
                    $cleanedSqlString .= ', ';
                }

                $value = $this->cleanQuote($value);
                $cleanedSqlString .= $value;
            }
        }

        return $cleanedSqlString;
    }

    /**
     * Generate a sql where condition
     *
     * @param $key
     * @param $options
     * @param $fromAlias
     *
     * @return string
     *
     * @throws \Barrage\DataSource\Relational\Exception\RelationalSqlException
     */
    private function getWhereCondition($key, $options, $fromAlias)
    {
        /**
         * Allows us to parse simple sql like where statements when used as values for fields.  Supported conditional types:
         *
         * =
         * !=
         * >
         * >=
         * <
         * <=
         * like
         * notlike
         * in
         * notin
         * between
         */
        if(is_string($options))
        {
            $statementParts = explode(' ', trim($options), 2);

            switch($statementParts[0])
            {
                case '=':
                case '!=':
                case '>':
                case '>=':
                case '<':
                case '<=':
                case 'like':
                case 'notlike':
                    $options = array
                    (
                        'condition' => $statementParts[0],
                        'value' => str_replace("'", '', $statementParts[1])
                    );
                    break;

                case 'in':
                case 'notin':
                    $condition = ($statementParts[0] == 'in') ? '=' : '!=';
                    $values = explode(',', $statementParts[1]);
                    $options = array
                    (
                        'condition' => $condition,
                        'value' => $values
                    );
                    break;

                case 'between':
                    $values = explode(' and ', $statementParts[1]);
                    $options = array
                    (
                        'condition' => $statementParts[0],
                        'value' => $values
                    );
                    break;

                default:
                    break;
            }
        }

        if(is_array($options) && (!empty($options['value']) || $options['value'] === null))
        {
            $condition = (!empty($options['condition'])) ? $options['condition'] : '=';
            $alias = (!empty($options['database'])) ? $options['database'] : $fromAlias;
            $value = $options['value'];

            if(!$this->validateWhereCondition($condition))
            {
                throw new RelationalSqlException("Invalid where condition given : {$condition}");
            }

            switch($condition)
            {
                case '=':
                    if($value !== null)
                    {
                        $condition = (is_array($value)) ? 'IN' : $condition;
                        $value = (is_array($value)) ? '(' . $this->arrayToCleanStringSql($value) . ')' : " '{$value}'";
                        $condition = $condition . $value;
                    }
                    else
                    {
                        $condition = 'IS NULL';
                    }

                    break;

                case '!=':
                    if($value !== null)
                    {
                        $condition = (is_array($value)) ? 'NOT IN' : $condition;
                        $value = (is_array($value)) ? '(' . $this->arrayToCleanStringSql($value) . ')' : " '{$value}'";
                        $condition = $condition . $value;
                    }
                    else
                    {
                        $condition = 'IS NOT NULL';
                    }

                    break;

                case 'between':
                    if(empty($value[0]) || empty($value[1]))
                    {
                        throw new RelationalSqlException("Between requires the value that is passed is an array with 2 values");
                    }

                    $value1 = $this->cleanQuote($value[0]);
                    $value2 = $this->cleanQuote($value[1]);
                    $condition = strtoupper($condition) . ' ' . $value1 . ' AND ' . $value2;

                    break;

                default:
                    $value = $this->cleanQuote($value);
                    $condition = strtoupper($condition) . ' ' . $value;

                    break;
            }
        }
        else
        {
            $alias = $fromAlias;

            if(is_array($options))
            {
                $value = $this->arrayToCleanStringSql($options);
                $condition = '= ' . $value;
            }
            else if($options === null)
            {
                $condition = 'IS NULL';
            }
            else
            {
                $value = $this->cleanQuote($options);
                $condition = '= ' . $value;
            }
        }

        //if the dot character (.) is not present, assume it is associated to the main table otherwise, assume the key is fully qualified and escaped properly
        if(strpos($key, '.') === false)
        {
            return "`{$alias}`.`{$key}` {$condition}";
        }
        else
        {
            return $key . ' ' . $condition;
        }
    }

    /**
     * Parses out the parts of a order value
     *
     * @param $value
     *
     * @return array
     */
    private function parseOrderValue($value)
    {
        $values = array();
        $value = trim($value);

        if(strpos($value, ' ') === false)
        {
            $values['key'] = $value;
            $values['sort'] = 'asc';
        }
        else
        {
            $splitValue = explode(' ', $value, 2);
            $values['key'] = $splitValue[0];
            $values['sort'] = $splitValue[1];
        }

        return $values;
    }

    /**
     * Retrieves detailed information about the table
     *
     * @param $table
     * @param null $database
     *
     * @return array
     */
    public function getTableFieldsDetails($table, $database = null)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;

        $sql = "DESCRIBE {$database}.{$table}";
        $tempData = $this->query($sql);

        $foreignKeys = $this->getForeignKeys($table, $database);

        $tableData = array();

        foreach($tempData as $fieldData)
        {
            $field = null;
            $fieldType = null;
            $fieldTypeDetailed = $fieldData['Type'];
            $keyType = 'none';
            $foreignTable = null;
            $unique = false;
            $required = false;
            $autoIncrement = false;

            $field = $fieldData['Field'];

            if(strpos($fieldData['Type'], '(') !== false)
            {
                $fieldType = substr($fieldData['Type'], 0, strpos($fieldData['Type'], '('));
            }
            else
            {
                $fieldType = $fieldData['Type'];
            }

            //check for foreign key
            if(!empty($foreignKeys[$field]))
            {
                $keyType = 'foreign';
                $foreignTable = explode('.', $foreignKeys[$field]);
                $foreignTable = $foreignTable[0];
            }

            if($fieldData['Key'] === 'PRI')
            {
                $keyType = 'primary';
            }

            if($fieldData['Null'] === 'NO' && ($fieldData['Default'] === null || $fieldData['Default'] === ''))
            {
                $required = true;
            }

            //we just need to find if this is a unique index
            $sql = "SHOW INDEXES FROM {$database}.{$table} WHERE Column_name = '{$field}'AND Non_unique = '0'";
            $indexes = $this->getAll($sql);

            if(count($indexes) > 0)
            {
                $unique = true;
            }

            if($fieldData['Extra'] == 'auto_increment')
            {
                $autoIncrement = true;
            }

            $tableData[] = array
            (
                'field' => $field,
                'field_type' => $fieldType,
                'field_type_detailed' => $fieldTypeDetailed,
                'key_type' => $keyType,
                'foreign_table' => $foreignTable,
                'unique' => $unique,
                'required' => $required,
                'auto_increment' => $autoIncrement
            );
        }

        return $tableData;
    }

    /**
     * Returns an array of the [field name] => [foreign table].[field_name] of all foreign keys
     *
     * @param $table
     * @param null|string $database Database name
     *
     * @return array
     */
    public function getForeignKeys($table, $database = null)
    {
        $return = array();
        $statement = $this->getCreateStatement($table, true, $database);

        foreach($statement as $part)
        {
            if(substr($part, 0, 11) != 'CONSTRAINT ')
            {
                continue;
            }

            $parts = explode(' ', $part);

            $field = substr($parts[4], 2, -2);
            $foreignTable = substr($parts[6], 1, -1);
            $foreignField = substr($parts[7], 2, -2);

            $return[$field] = $foreignTable . '.' . $foreignField;
        }

        return $return;
    }

    /**
     * Returns the create statement for a table.
     *
     * @param $table
     * @param bool $asLineArray
     * @param null $database
     *
     * @return array
     */
    public function getCreateStatement($table, $asLineArray = false, $database = null)
    {
        $query = "SHOW CREATE TABLE {$database}.{$table}";
        $statement = $this->getRow($query);
        $statement = $statement['Create Table'];

        if($asLineArray)
        {
            $parts = explode("\n", $statement);
            $statement = array();

            foreach($parts as $part)
            {
                $statement[] = trim($part);
            }
        }

        return $statement;
    }

    /**
     * Returns the details for index on a table or field
     *
     * @param $tableName
     * @param null $fieldName
     * @param null $database
     *
     * @todo Implementation
     *
     * @return array
     */
    public function getIndexes($tableName, $fieldName = null, $database = null)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;

		$query = "SHOW INDEXES FROM {$database}.{$tableName}";

		if($fieldName != null)
		{
			$query .= " WHERE Column_name = '{$fieldName}'";
		}

		$tempData = $this->getAll($query);

		foreach($tempData as $indexData)
		{
			$table = null;
			$field = null;
			$indexName = null;
			$unique = false;
			$allowNull = false;


		}

		$indexes = $tempData;
		return $indexes;
    }

    /**
     * All possible options for the enum or set type field.
     *
     * @param $tableName
     * @param $fieldName
     * @param null $database
     *
     * @return array
     */
    public function getFieldValues($tableName, $fieldName, $database = null)
    {
        $tableDetails	= $this->getTableFieldsDetails($tableName, $database);
		$fieldType		= null;
		$return			= array();

		foreach($tableDetails as $details)
		{
			if($details['field'] === $fieldName)
			{
				$fieldType = $details['field_type'];
                $fieldTypeDetailed = $details['field_type_detailed'];
			}
		}

		if($fieldType == 'enum' || $fieldType == 'set')
		{
			$options = explode(',', str_replace("'", '', substr($fieldTypeDetailed, (strlen($fieldType) + 1), -1)));

			foreach($options as $value)
			{
			    $return[] = $value;
			}

			return $return;
		}
		else
		{
			return array();
		}
    }

    /**
     * Returns all tables in the database.
     *
     * @param string optional database name(default is null which uses store database name)
     *
     * @return mixed[]| Table names or false if database could not be found
     */
    public function getTables($database = null)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;

		$query = "SHOW TABLES FROM {$database}";
		return $this->getColumn($query);
    }
}
