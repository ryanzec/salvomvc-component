<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\DataSource\Relational;

use Barrage\Configuration;
use Barrage\DataSource\IConnectionDataFactory;

/**
 * Factory for generating instances of IConnectionData
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class ConnectionDataFactory implements IConnectionDataFactory
{
    /**
     * @static
     * @param $configurationName
     * @return IConnectionData
     */
    public static function buildFromConfiguration($configurationName)
    {
        $dataSourceConfiguration = Configuration::getDataSourceConfiguration($configurationName);
        $connectionDataClassName = 'Barrage\\DataSource\\Relational\\Driver\\' . $dataSourceConfiguration['driver'] . '\\' . 'ConnectionData';
        $host = $dataSourceConfiguration['host'];
        $username = $dataSourceConfiguration['username'];
        $password = $dataSourceConfiguration['password'];
        $database = $dataSourceConfiguration['database'];
        $port = (!empty($dataSourceConfiguration['port'])) ? $dataSourceConfiguration['port'] : null;
        $options = (!empty($dataSourceConfiguration['options'])) ? $dataSourceConfiguration['options'] : array();

        return new $connectionDataClassName($host, $username, $password, $database, $port, $options);
    }
}
