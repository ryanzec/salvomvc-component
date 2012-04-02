<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\DataSource\Relational;

use Salvo\Barrage\DataSource\Relational\IDataSource;
use Salvo\Barrage\DataSource\Relational\IConnectionData;
use Salvo\Barrage\DataSource\Relational\Exception\RelationalSqlException;
use Salvo\Barrage\DataSource\Relational\Exception\ConnectionException;

/**
 * Base data source object for all relational sql data source the support PDO
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
abstract class BaseDataSource implements IDataSource
{
    /**
     * @var IConnectionData
     */
    protected $connectionData;

    /**
     * @var \PDO
     */
    protected $pdoConnection;

    /**
     * @var \PDOStatement
     */
    protected $lastPdoStatement;

    /**
     * @var int
     */
    protected $lastRowsEffected;

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var string
     */
    protected $defaultDatabase;

    /**
     * @var bool
     */
    protected $transactionStarted;

    /**
     * Valid where conditions
     *
     * @var array
     */
    protected static $validWhereConditions = array('=', '!=', '>', '>=', '<', '<=', 'like', 'not like', 'between');

    /**
     * Valid where logic
     *
     * @var array
     */
    protected static $validWhereLogic = array('and', 'or');

    /**
     * @var array
     */
    private static $instances;

    protected static $displayQueries = false;

    /**
     * Setup the PDO connection with the passed configuration
     *
     * @param IConnectionData $connectionData
     * @return BaseDataSource
     */
    private function __construct(IConnectionData $connectionData)
    {
        $this->connectionData = $connectionData;
        $this->defaultDatabase = $this->connectionData->getDefaultDatabase();
        $this->initializeConnection();
    }

    /**
     * Release any resources available
     */
    public function __destruct()
    {
        $this->lastPdoStatement = null;
        $this->pdoConnection = null;
    }

    public static function getInstance(IConnectionData $connectionData)
    {
        $key = $connectionData->__toString();
        if(!isset(self::$instances[$key]) || !self::$instances[$key] instanceof BaseDataSource)
        {
            self::$instances[$key] = new static($connectionData);
        }

        return self::$instances[$key];
    }

    /**
     * Returns that connection data object for the data source
     *
     * @return IConnectionData
     */
    public function getConnectionData()
    {
        return $this->connectionData;
    }

    /**
     * Retrieve all the results of a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     * @param mixed $returnType optional The type of data returned (default to PDO::FETCH_ASSOC)
     *
     * @return mixed[][] The results
     */
    public function getAll($sql, $returnType = \PDO::FETCH_ASSOC)
    {
		if($this->query($sql))
		{
			if($this->lastPdoStatement !== false)
			{
				return $this->lastPdoStatement->fetchAll($returnType);
			}
		}

		return false;
    }

    /**
     * Return the first row returned by a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     * @param int $returnType optional The type of data returned (default to PDO::FETCH_ASSOC)
     *
     * @return mixed[] The results
     */
    public function getRow($sql, $returnType = \PDO::FETCH_ASSOC)
    {
		if($this->query($sql))
		{
			if($this->lastPdoStatement !== false)
			{
				$record = $this->lastPdoStatement->fetch($returnType, \PDO::FETCH_ORI_FIRST);

				//we need to close off the query in order to allow for more query to be executed
				$this->lastPdoStatement->closeCursor();

				return $record;
			}
		}

		return false;
    }

    /**
     * Returns all the values for the first column for all the returned rows as a single dimension array from a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     *
     * @return mixed[] The results
     */
    public function getColumn($sql)
    {
        if($this->query($sql))
		{
	        if($this->lastPdoStatement !== false)
			{
				$return = array();

                foreach($this->lastPdoStatement as $row)
				{
					$return[] = $row[0];
				}

				return $return;
			}
		}

		return false;
    }

    /**
     * Return the first column from the first returned row from a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     *
     * @return mixed The results
     */
    public function getOne($sql)
    {
		if($this->query($sql))
		{
			if($this->lastPdoStatement !== false)
			{
				foreach($this->lastPdoStatement as $row)
				{
					return $row[0];
				}
			}
		}

		return false;
    }

    /**
     * Executes a sql call
     *
     * @abstract
     * @param string $sql The sql to execute
     *
     * @return \PDOStatement
     */
    function query($sql)
    {
        $this->setSql($sql);
        $this->lastPdoStatement = $this->pdoConnection->query($this->sql);

        if($this->lastPdoStatement !== false)
        {
            $this->lastRowsEffected = $this->lastPdoStatement->rowCount();
        }
        else
        {
            $this->databaseError();
        }

        return $this->lastPdoStatement;
    }

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
    function insert($table, $data, $database = null)
    {
        $sql = $this->simpleInsertBuilder($table, $data, $database);
        $this->query($sql);

        //return the id of the inserted record
        return $this->getLastInsertId();
    }

    /**
     * Returns the last insert id for the connection
     *
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->pdoConnection->lastInsertId();
    }

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
    function update($table, $data, $where, $database = null)
    {
        $sql = $this->simpleUpdateBuilder($table, $data, $where, $database);
        return $this->query($sql);
    }

    /**
     * Cleans the input to protected against sql injections
     *
     * @abstract
     * @param mixed $value The value to clean
     * @param bool $wrapQuotes optional Whether you want to have the return value already in quotes or not
     *
     * @return string Cleaned value
     */
    function cleanQuote($value, $wrapQuotes = true)
    {
        if(is_array($value))
        {
            throw new RelationalSqlException("Can't clean array variable");
        }

		$value = $this->pdoConnection->quote($value);

		if($wrapQuotes === false)
		{
			$value = substr($value, 1, -1);
		}

		return $value;
    }

    /**
     * Create the PDO connection to the database
     *
     * @throws ConnectionException
     *
     * @return void
     */
    private function initializeConnection()
    {
        try
        {
            $this->pdoConnection = new \PDO($this->connectionData->getConnectionString(),
                                            $this->connectionData->getUsername(),
                                            $this->connectionData->getPassword(),
                                            $this->connectionData->getOptions());
        }
        catch(\Exception $exception)
        {
            throw new ConnectionException("Unable to make PDO Database connection ({$exception->getMessage()})", $this->connectionData);
        }
    }

    /**
     * Throws an exception with detailed PDO exception information if available
     *
     * @throws RelationalSqlException
     *
     * @return void
     */
    private function databaseError()
    {
        $error_information = $this->pdoConnection->errorInfo();

        if(!empty($error_information[0]))
        {
            $exception_message = 'PDO SQL State Error Code: ' . $error_information[0] . "\n"
            . ucfirst($this->getServerVendorName()) . ' error code: ' . $error_information[1] . "\n"
            . 'Error Details: ' . $error_information[2] . "\n"
            . 'SQL: ' . $this->sql;

            throw new RelationalSqlException($exception_message, $this->sql);
        }

		throw new RelationalSqlException("Unknown database exception happened", $this->sql);
    }

    /**
     * Sets the sql the is used fro execution and retrieval
     *
     * @param $sql string The sql to store
     *
     * todo: add functionality to be able to show queries as they run
     *
     * @return void
     */
    private function setSql($sql)
    {
        if(static::$displayQueries === true)
        {
            var_dump($sql);
        }

        $this->sql = $sql;
        return true;
    }

    /**
     * Commits a transaction
     *
     * @abstract
     * @return void
     */
	public function commitTransaction()
	{
        if(!$this->pdoConnection->commit())
        {
            throw new RelationalSqlException("Unable to commit transaction for unknown reasons");
        }

		$this->transactionStarted = false;
	}

    /**
     * Rolls back a transaction
     *
     * @abstract
     * @return void
     */
	public function rollBackTransaction()
	{
        if(!$this->pdoConnection->rollBack())
        {
            throw new RelationalSqlException("Unable to rollback transactions for unknown reasons");
        }

		$this->transactionStarted = false;
	}

    /**
     * Starts a transaction
     *
     * @abstract
     * @return void
     */
	public function startTransaction()
	{
        //you can not run mutliple transactions at once.
		if($this->transactionStarted == true)
		{
            throw new RelationalSqlException("Unable to start a new transaction as another one is already in progress");
        }

        if(!$this->pdoConnection->beginTransaction())
        {
            throw new RelationalSqlException("Unable to start transaction for unknown reasons");
        }

	    $this->transactionStarted = true;
	}

    /**
     * Returns the raw PDO connection
     *
     * @return \PDO
     */
    public function getPdoConnection()
    {
        return $this->pdoConnection;
    }

    /**
     * Validates if the condition is a valid one
     *
     * @param $condition
     *
     * @return bool
     */
    protected function validateWhereCondition($condition)
    {
        return in_array($condition, self::$validWhereConditions);
    }

    /**
     * Validates if the logic is a valid one
     *
     * @param $logic
     *
     * @return bool
     */
    protected function validateWhereLogic($logic)
    {
        return in_array($logic, self::$validWhereLogic);
    }

    protected function getDatabaseName($database)
    {
        $database = (!empty($database)) ? $database : $this->defaultDatabase;
        //return $database;
        return \Salvo\Barrage\Configuration::getRealDatabaseName($database);
    }

    public static function displayQueries()
    {
        static::$displayQueries = true;
    }

    public static function hideQueries()
    {
        static::$displayQueries = false;
    }
}
