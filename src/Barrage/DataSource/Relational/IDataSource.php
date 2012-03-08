<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\DataSource\Relational;

/**
 * Interface for data source objects
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
interface IDataSource
{
    /**
     * Returns that connection data object for the data source
     *
     * @abstract
     *
     * @return IConnectionData
     */
    public function getConnectionData();

    /**
     * Retrieve all the results of a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     * @param int $returnType optional The type of data returned (default to PDO::FETCH_ASSOC)
     *
     * @return mixed[][] The results
     */
    function getAll($sql, $returnType = \PDO::FETCH_ASSOC);

    /**
     * Return the first row returned by a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     * @param int $returnType optional The type of data returned (default to PDO::FETCH_ASSOC)
     *
     * @return mixed[] The results
     */
    function getRow($sql, $returnType = \PDO::FETCH_ASSOC);

    /**
     * Returns all the values for the first column for all the returned rows as a single dimension array from a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     *
     * @return mixed[] The results
     */
    function getColumn($sql);

    /**
     * Return the first column from the first returned row from a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     *
     * @return mixed The results
     */
    function getOne($sql);

    /**
     * Executes a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     *
     * @return \PDOStatement
     */
    function query($sql);

    /**
     * Generates a sql call for selecting data
     * NOTE: This is designed to be used with the active record system only, not designed as a general use query builder
     *
     * @abstract
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
    function simpleSelectBuilder($select, $from, $join = array(), $where = array(), $group = array(), $order = array(), $limit = null, $offset = 0);

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
    function simpleInsertBuilder($table, $data, $database = null);

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
    function simpleUpdateBuilder($table, $data, $where, $database = null);

    /**
     * Inserts data into the database
     *
     * @abstract
     * @param string $table The table to insert into
     * @param mixed[] $data the data to insert
     * @param null|string $database optional The database the table is in (default to connection's default database)
     *
     * @return mixed Id of the newly inserted record
     */
    function insert($table, $data, $database = null);

    /**
     * Returns the last insert id for the connection
     *
     * @abstract
     *
     * @return string
     */
    public function getLastInsertId();

    /**
     * Updates data in the database
     *
     * @abstract
     * @param string $table The table to update
     * @param mixed[] $data The data tp update
     * @param string $where The where part of the statement that comes after WHERE in the sql call
     * @param null|string $database optional The database the table is in (default to connection's default database)
     *
     * @return void
     */
    function update($table, $data, $where, $database = null);

    /**
     * Cleans the input to protected against sql injections
     *
     * @abstract
     * @param mixed $value The value to clean
     * @param bool $wrapQuotes optional Whether you want to have the return value already in quotes or not
     *
     * @return string Cleaned value
     */
    function cleanQuote($value, $wrapQuotes = true);

    /**
     * Return the name of the databse vendor
     *
     * @abstract
     *
     * @return string The name of the database vendor
     */
    function getServerVendorName();

    /**
     * Commits a transaction
     *
     * @abstract
     * @return void
     */
	public function commitTransaction();

    /**
     * Rolls back a transaction
     *
     * @abstract
     * @return void
     */
	public function rollBackTransaction();

    /**
     * Starts a transaction
     *
     * @abstract
     * @return void
     */
	public function startTransaction();

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
    public function delete($table, $where, $database = null);

    /**
     * Retrieves detailed information about the table
     *
     * @abstract
     *
     * @param $table
     * @param null $database
     */
    public function getTableFieldsDetails($table, $database = null);


    /**
     * Returns an array of the field name => foreign table of all foreign keys
     *
     * @abstract
     *
     * @param $table
     * @param null $database
     */
    public function getForeignKeys($table, $database = null);


    /**
     * Returns the create statement for a table.
     *
     * @abstract
     *
     * @param $table
     * @param bool $asLineArray
     * @param null $database
     */
    public function getCreateStatement($table, $asLineArray = false, $database = null);

    /**
     * Returns the details for index on a table or field
     *
     * @abstract
     *
     * @param $tableName
     * @param null $fieldName
     * @param null $database
     */
	public function getIndexes($tableName, $fieldName = null, $database = null);


    /**
     * All possible options for the enum or set type field.
     *
     * @abstract
     *
     * @param $tableName
     * @param $fieldName
     * @param null $database
     */
	public function getFieldValues($tableName, $fieldName, $database = null);

    /**
	* Returns all tables in the database.
	*
	* @abstract
	*
	* @param string optional database name(default is null which uses store database name)
	* @return mixed[]| Table names or false if database could not be found
	*/
	public function getTables($database = null);
}
