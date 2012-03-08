<?php
namespace Salvo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class BarrageServiceProvider implements ServiceProviderInterface
{
    public function register(Application $application)
    {
        //setup the autoloader
        if(!isset($application['barrage.class_path']))
        {
            throw new \Exception('Unable to load class path for the Barrage');
        }

        //$application['autoloader']->registerNamespace('Barrage', $application['barrage.class_path']);

        //barrage configuration file is required for barrage to work
        if(!isset($application['barrage.configuration_file_path']))
        {
            throw new \Exception('Configuration file path not given for Barrage');
        }
        else if(!file_exists($application['barrage.configuration_file_path']))
        {
            throw new \Exception("Unable to locate the configuration file given for Barrage ({$application['barrage.configuration_file_path']})");
        }

        \Barrage\Configuration::load($application['barrage.configuration_file_path']);

        //barrage console configuration file is required for barrage to work
        if(!isset($application['barrage.console_configuration_file_path']))
        {
            throw new \Exception('Console configuration file path not given for Barrage');
        }
        else if(!file_exists($application['barrage.console_configuration_file_path']))
        {
            throw new \Exception("Unable to locate the console configuration file given for Barrage ({$application['barrage.console_configuration_file_path']})");
        }

        \Barrage\Configuration::load($application['barrage.console_configuration_file_path']);
    }
}
