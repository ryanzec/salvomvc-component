<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\DataSource;

/**
 * Interface for data source factory classes
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
interface IDataSourceFactory
{
    /**
     * Returns a DataSource object built from a configuration
     *
     * @abstract
     * @param $configurationName
     */
    public static function buildFromConfiguration($configurationName);
}
