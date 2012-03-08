<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\DataSource\Relational\Driver\Mysql;

use Barrage\DataSource\Relational\IConnectionData;

/**
 * Object that has all the required data to make a connection to a MySQL server
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class ConnectionData implements IConnectionData
{
    /**
     * The host name fo the server
     *
     * @var string
     */
    private $host;

    /**
     * The username for the connection
     *
     * @var string
     */
    private $username;

    /**
     * The password for the connection
     *
     * @var string
     */
    private $password;

    /**
     * $the name of the default database to connect to
     *
     * @var string
     */
    private $database;

    /**
     * Any PDO options the connection might need
     *
     * @var array
     */
    private $options;

    /**
     * The port number mysql is listening on
     *
     * @var int
     */
    private $port;

    /**
     * The default port for mysql
     *
     * @var int
     */
    const DEFAULT_PORT = 3306;

    /**
     * The name of database vendor
     *
     * @var string
     */
    public static $driver = "mysql";

    /**
     * Constructor
     *
     * @param string $host The host of the server
     * @param string $username The connection username
     * @param string $password The connection password
     * @param string $database The database to connect to by default
     * @param int|null $port The port the server is listen to
     * @param array $options PDO options
     * @return \Barrage\DataSource\Relational\Driver\Mysql\ConnectionData
     */
    public function __construct($host, $username, $password, $database, $port = null, $options = array())
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = (!empty($port)) ? $port : self::DEFAULT_PORT;

        $convertedOptions = array();

        if(!empty($options))
        {
            foreach($options as $option => $value)
            {
                $convertedOptions[constant($option)] = $value;
            }
        }

        $this->options = $convertedOptions;
    }

    /**
     * Converts a object to a uniquely identifiable string
     *
     * @abstract
     *
     * @return string
     */
    public function __toString()
    {
        return implode(':', array(self::$driver, $this->host, $this->username, $this->database));
    }

    /**
     * Returns the connection string needed in order to connection to a relational database
     *
     * @return string The connection string
     */
    public function getConnectionString()
    {
        return 'mysql:dbname=' . $this->database . ';host=' . $this->host . ';port=' . $this->port;
    }

    /**
     * Returns the username used to connect to this databsae
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the password used to connect to this database
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns any options there might be for this connection
     *
     * @abstract
     *
     * @return mixed[] The options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the default database this connection should connect to
     *
     * @return string The default database
     */
    public function getDefaultDatabase()
    {
        return $this->database;
    }
}
