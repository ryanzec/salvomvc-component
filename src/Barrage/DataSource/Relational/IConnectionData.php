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
 * Interface for connection data objects
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
interface IConnectionData
{
    /**
     * Converts a object to a uniquely identifiable string
     *
     * @abstract
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the connection string needed in order to connection to a relational database
     *
     * @abstract
     *
     * @return string The connection string
     */
    public function getConnectionString();

    /**
     * Returns the username used to connect to this data source
     *
     * @abstract
     *
     * @return string The username
     */
    public function getUsername();

    /**
     * Returns the password used to connect to this data source
     *
     * @abstract
     *
     * @return string The password
     */
    public function getPassword();

    /**
     * Returns any options there might be for this connection
     *
     * @abstract
     *
     * @return mixed[] The options
     */
    public function getOptions();

    /**
     * Returns the default database this connection should connect to
     *
     * @abstract
     *
     * @return string The default database
     */
    public function getDefaultDatabase();
}
