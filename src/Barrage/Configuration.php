<?php
/**
 * This is part of the Barrage data abstraction layer.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage;

use Symfony\Component\Yaml\Yaml;

/**
 * Configuration object
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class Configuration
{
    /**
     * configuration data
     *
     * @var array
     */
    private static $configuration = array();

    /**
     * Loads the passed yaml configuration file
     *
     * @static
     *
     * @param $pathToFile
     */
    public static function load($pathToFile)
    {
        $configFileArray = Yaml::parse($pathToFile);

        if(is_array($configFileArray))
        {
            self::$configuration = array_merge(Yaml::parse($pathToFile), self::$configuration);
        }
    }

    /**
     * Returns a particular option of the configuration
     *
     * @static
     *
     * @param $key
     *
     * @return null
     */
    public static function getOption($key)
    {
        if(!empty(self::$configuration[$key]))
        {
            return self::$configuration[$key];
        }

        return null;
    }

    public static function getAll()
    {
        return self::$configuration;
    }

    /**
     * Returns a specific data sources configuration
     *
     * @static
     *
     * @param $configuration
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function getDataSourceConfiguration($configuration)
    {
        if(empty(self::$configuration['data_source'][$configuration]))
        {
            throw new \Exception("Unable to find '{$configuration}' data source configuration");
        }

        return self::$configuration['data_source'][$configuration];
    }

    /**
     * Manually data a configuration option through php
     * NOTE: Setting a configuration option with this method with only effect that one instance and after the script ends, that option will not persist to the next request
     *
     * @param $key
     * @param $data
     */
    public function setOption($key, $data)
    {
        self::$configuration[$key] = $data;
    }

    public static function getTableAlias($databaseName, $tableName)
    {
        if(
            empty(self::$configuration['model_builder']['relational']['databases'][$databaseName])
            || empty(self::$configuration['model_builder']['relational']['databases'][$databaseName]['tables'][$tableName])
            || empty(self::$configuration['model_builder']['relational']['databases'][$databaseName]['tables'][$tableName]['alias'])
          )
        {
            throw new \Exception("Alias not configured for {$databaseName}.{$tableName}");
        }

        return self::$configuration['model_builder']['relational']['databases'][$databaseName]['tables'][$tableName]['alias'];
    }
}
