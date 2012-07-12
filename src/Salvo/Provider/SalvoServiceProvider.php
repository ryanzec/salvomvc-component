<?php
namespace Salvo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Salvo\Extension;

class SalvoServiceProvider implements ServiceProviderInterface
{
    public function register(Application $application)
    {
        $applicationConfig = $application['app_config'];
        $providerConfig = $applicationConfig['service_providers']['salvo'];
        //load twig extensions
        $application['twig']->addExtension(new Extension\Twig($application));

        //see if we need to register the unit test namespace
        if(defined('UNIT_TESTING') && UNIT_TESTING === true)
        {
            $applicationConfig['autoloader']->registerNamespace('SalvoTests', __DIR__ . '/../vendor/salvo/salvo/tests');
        }

        //barrage configuration file is required for barrage to work
        if(!isset($providerConfig['barrage_configuration_file_path']))
        {
            throw new \Exception('Configuration file path not given for Barrage');
        }
        else if(!file_exists(SALVO_ROOT_PATH . '/' . $providerConfig['barrage_configuration_file_path']))
        {
            $filePath = SALVO_ROOT_PATH . '/' . $providerConfig['barrage_configuration_file_path'];
            throw new \Exception("Unable to locate the configuration file given for Barrage ({$filePath})");
        }

        \Salvo\Barrage\Configuration::load(SALVO_ROOT_PATH . '/' . $providerConfig['barrage_configuration_file_path']);

        //barrage console configuration file is required for barrage to work
        if(!isset($providerConfig['barrage_console_configuration_file_path']))
        {
            throw new \Exception('Console configuration file path not given for Barrage');
        }
        else if(!file_exists(SALVO_ROOT_PATH . '/' . $providerConfig['barrage_console_configuration_file_path']))
        {
            $filePath = SALVO_ROOT_PATH . '/' . $providerConfig['barrage_console_configuration_file_path'];
            throw new \Exception("Unable to locate the console configuration file given for Barrage ({$filePath})");
        }

        \Salvo\Barrage\Configuration::load(SALVO_ROOT_PATH . '/' . $providerConfig['barrage_console_configuration_file_path']);

        $application['session']->start();
    }

    public function boot(Application $application)
    {

    }
}
