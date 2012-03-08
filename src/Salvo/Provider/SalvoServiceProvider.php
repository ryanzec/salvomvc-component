<?php
namespace Salvo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Salvo\Extension;

class SalvoServiceProvider implements ServiceProviderInterface
{
    public function register(Application $application)
    {
        //load twig extensions
        $application['twig']->addExtension(new Extension\Twig($application));

        //see if we need to register the unit test namespace
        if(defined('UNIT_TESTING') && UNIT_TESTING === true)
        {
            $application['autoloader']->registerNamespace('SalvoTests', __DIR__ . '/../vendor/salvo/salvo/tests');
        }

        //barrage configuration file is required for barrage to work
        if(!isset($application['salvo.barrage_configuration_file_path']))
        {
            throw new \Exception('Configuration file path not given for Barrage');
        }
        else if(!file_exists($application['salvo.barrage_configuration_file_path']))
        {
            throw new \Exception("Unable to locate the configuration file given for Barrage ({$application['salvo.barrage_configuration_file_path']})");
        }

        \Salvo\Barrage\Configuration::load($application['salvo.barrage_configuration_file_path']);

        //barrage console configuration file is required for barrage to work
        if(!isset($application['salvo.barrage_console_configuration_file_path']))
        {
            throw new \Exception('Console configuration file path not given for Barrage');
        }
        else if(!file_exists($application['salvo.barrage_console_configuration_file_path']))
        {
            throw new \Exception("Unable to locate the console configuration file given for Barrage ({$application['salvo.barrage_console_configuration_file_path']})");
        }

        \Salvo\Barrage\Configuration::load($application['salvo.barrage_console_configuration_file_path']);
    }
}
