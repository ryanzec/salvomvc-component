<?php
/**
 * This is part of the Salvo framework.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo;

use Salvo\Utility\ClassHelper;
use Silex\Application;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Main Salvo application object
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class Salvo
{
    /**
     * Instance of the Sile\Application object
     *
     * @var \Silex\Application
     */
    private static $application;

    /**
     * Path of the root of Salvo
     *
     * @var string
     */
    private static $applicationRootDirectory;

    /**
     * Self instance
     *
     * @var Salvo
     */
    private static $instance;

    /**
     * Setup of the basic configuration for Silex and Salvo
     *
     * @param $application
     * @param $applicationRootDirectory
     */
    private function __construct(Application $application, $applicationRootDirectory)
    {
        self::$applicationRootDirectory = $applicationRootDirectory;

        //load configurations
        $application['app_config'] = Yaml::parse(self::$applicationRootDirectory . '/configuration/application.yml');
        $application['routes_config'] = Yaml::parse(self::$applicationRootDirectory . '/configuration/routes.yml');

        #selix configurations
        $application['debug'] = $application['app_config']['debug'];

        //setup the root redirect using the app_config setting
        $application->get('/', function() use ($application)
        {
            return $application->redirect($application['routes_config']['root_redirect']);
        });

        //setup global routes
        if(!empty($application['routes_config']['global_routes']))
        {
            foreach($application['routes_config']['global_routes'] as $name => $route)
            {
                if(empty($application['routes_config'][$route['class_name']]['namespaced_class']))
                {
                    throw new \Exception('No namespaced class configured for class ' . $route['class_name']);
                }

                $namespacedClass = $application['routes_config'][$route['class_name']]['namespaced_class'];
                $method = (!empty($route['method'])) ? $route['method'] : 'get';
                $controllerAction = $namespacedClass . '::' . $route['action'] . 'Action';
                $application->$method($route['route'], $controllerAction)->bind($name);
            }
        }

        //load all configured services providers
        if(is_array($application['app_config']['service_providers']) && !empty($application['app_config']['service_providers']))
        {
            foreach($application['app_config']['service_providers'] as $serviceName => $serviceConfiguration)
            {
                //build the parameter list
                $parameters = array();

                if(!empty($serviceConfiguration['parameters']) && is_array($serviceConfiguration['parameters']))
                {
                    foreach($serviceConfiguration['parameters'] as $key => $value)
                    {
                        $parameters[$serviceName . '.' . $key] = (($key == 'path' || strpos($key, '_path') !== false) && substr($value, 0, 1) != '/')
                            ? self::$applicationRootDirectory . '/' . $value
                            : $value;
                    }
                }

                if(!empty($serviceConfiguration['options']) && is_array($serviceConfiguration['options']))
                {
                    $parameters[$serviceName . '.options'] = $serviceConfiguration['options'];
                }

                //include the service provider file for provider not included in silex itself
                if(!empty($serviceConfiguration['file_path']))
                {
                    require_once(self::$applicationRootDirectory . '/' . $serviceConfiguration['file_path']);
                }

                //register the service provider
                $serviceClassName = $serviceConfiguration['class_name'];
                $application->register(new $serviceClassName(), $parameters);
            }
        }

        //mount all configured controllers
        if(is_array($application['routes_config']) && !empty($application['routes_config']))
        {
            foreach($application['routes_config'] as $key => $controllerConfiguration)
            {
                //only process configuration ending in Controller
                if(substr($key, -10) == 'Controller')
                {
                    $class = $controllerConfiguration['namespaced_class'];
                    $application->mount('/' . $controllerConfiguration['base_route'], new $class());
                }
            }
        }

        //setup all the logging
        if(is_array($application['app_config']['loggers']) && !empty($application['app_config']['loggers']))
        {
            foreach($application['app_config']['loggers'] as $name => $settings)
            {
                $application[$name] = $application->share(function() use ($name, $settings, $applicationRootDirectory)
                {
                    //determine the logging level
                    $level = (!empty($settings['level'])) ? 'Monolog\Logger::' . strtoupper($settings['level']) : 'Monolog\Logger::CRITICAL';
                    $level = constant($level);
                    $logger = new Logger($name);
                    $logger->pushHandler(new StreamHandler($applicationRootDirectory . '/' . $settings['file_path'], $level));
                    return $logger;
                });
            }
        }

        $application->before(function($event) use ($application)
        {
            //make sure that the controller in a class
            $controller = $event->attributes->get('_controller');
            $application['requested_controller'] = null;
            $application['requested_action'] = null;

            //var_dump($event);

            if(strpos($controller, '::') !== false)
            {
                //set the requested controller and action
                $controllerParts = explode('::', $controller);

                $application['requested_controller'] = substr(ClassHelper::getNonNamespacedClass($controllerParts[0]), 0, -10);
                $application['requested_action'] = substr($controllerParts[1], 0, -6);
            }
        }, 1000);

        //make sure the session is saved
        $application->after(function($event) use ($application)
        {
            $application['session']->save();
        }, -1000);

        self::$application = $application;

        //apply bootstrap objects
        if(!empty($application['app_config']['bootstrap']))
        {
            foreach($application['app_config']['bootstrap'] as $bootstrap)
            {
                $this->applyBootstrap(new $bootstrap['class_name']());
            }
        }
    }

    /**
     * Return the instance of itself
     *
     * @static
     *
     * @param $application
     * @param $applicationRootDirectory
     *
     * @return Salvo
     */
    public static function getInstance($application, $applicationRootDirectory)
    {
        if(!self::$instance instanceof Salvo)
        {
            self::$instance = new self($application, $applicationRootDirectory);
        }

        return self::$instance;
    }

    /**
     * Return the instance of the Silex\Application object
     *
     * @return \Silex\Application
     */
    public function getSilexApplication()
    {
        return self::$application;
    }

    /**
     * Applies code from a bootstrap object
     *
     * @param IBootstrap $bootstrap Bootstrap object to apply
     *
     * @return void
     */
    public function applyBootstrap(IBootstrap $bootstrap)
    {
        $bootstrap->configure(self::$application);
    }

    /**
     * Executes the Silex\Application->run() method
     *
     * @return void
     */
    public function run()
    {
        self::$application->run();
    }
}

