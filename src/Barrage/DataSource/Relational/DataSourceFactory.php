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
use Barrage\DataSource\IDataSourceFactory;

/**
 * Factory for generating instances of IDataSource
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class DataSourceFactory implements IDataSourceFactory
{
    /**
     * @static
     * @param $configurationName
     * @return IDataSource
     */
    public static function buildFromConfiguration($configurationName)
    {
        //get the connection data object
        $connectionData = ConnectionDataFactory::buildFromConfiguration($configurationName);

        //figure out which driver we need to build the data source object from
        $dataSourceConfiguration = Configuration::getDataSourceConfiguration($configurationName);
        $dataSourceClassName = 'Barrage\\DataSource\\Relational\\Driver\\' . $dataSourceConfiguration['driver'] . '\\' . 'DataSource';

        return $dataSourceClassName::getInstance($connectionData);
    }
}
