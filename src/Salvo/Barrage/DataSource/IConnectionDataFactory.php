<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Barrage\DataSource;

/**
 * Interface for connection data factory classes
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
interface IConnectionDataFactory
{
    /**
     * Returns a ConnectionData object built from a configuration
     *
     * @abstract
     * @param $configurationName
     */
    public static function buildFromConfiguration($configurationName);
}
