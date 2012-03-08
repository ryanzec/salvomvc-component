<?php
/**
 * This is part of the Salvo framework.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Salvo\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\Yaml\Yaml;
use Salvo\Utility\RegexHelper;
use Salvo\Utility\ClassHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * The base controller all other Salvo controller should extend from
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class BaseController implements ControllerProviderInterface
{
    /**
     * Instance of the twig environment object
     *
     * @var \Twig_Environment
     */
    private static $twig;

    /**
     * Instance of the twig environment object
     *
     * @var \Twig_Environment
     */
    private static $session;

    /**
     * Relative path to global layout template
     *
     * @var Symfony\Component\HttpFoundation\Session
     */
    private static $globalLayoutTemplate;

    /**
     * Relative path to an override layout template if available
     *
     * @var null|string
     */
    private $overrideLayoutTemplate = null;

    /**
     * Global css files to include
     *
     * @var array
     */
    private $cssFiles = array();

    /**
     * Global javascript files to include
     *
     * @var array
     */
    private $javascriptFiles = array();

    /**
     * The name full name of the model to be used with the built-in rest functionality to provide basic create, read, update, delete and listing functionality
     *
     * @var null|string
     */
    private $restFullModelName = null;

    private $restObjectNameSingular = 'object';

    private $restObjectNamePlural = 'objects';

    /**
     * Sets up the controller configurations for Silex (like routing)
     *
     * @param \Silex\Application $application
     *
     * @return \Silex\ControllerCollection
     *
     * @throws \Exception
     */
    public function connect(Application $application)
    {
        self::$twig = $application['twig'];
        self::$session = $application['session'];
        self::$globalLayoutTemplate = $application['app_config']['global_layout_template'];

        $autoRoutes = $this->generateActionRoutes();
        $controllers = new ControllerCollection();

        //get information needed to dynamically configure routes
        $fullCalledClassName = get_called_class();
        $baseCalledClassName = $this->getNonNamespacedCalledClass();

        //makes sure this controller has routes configured
        if(empty($application['routes_config'][$baseCalledClassName]['routes']) && empty($application['routes_config'][$baseCalledClassName]['default_route']))
        {
            throw new \Exception("Not routes have been define for {$baseCalledClassName}");
        }

        $controllerRoutes = $application['routes_config'][$baseCalledClassName];

        //setup default page
        $controllers->get('/', function(Application $application) use ($controllerRoutes)
        {
            return $application->redirect($application['url_generator']->generate($controllerRoutes['default_route']));
        });

        //load the routes for this particular controller
        if(!empty($controllerRoutes['routes']))
        {
            foreach($controllerRoutes['routes'] as $route)
            {
                if(in_array($route['action'], $autoRoutes))
                {
                    $autoRoutes = array_diff($autoRoutes, array($route['action']));
                }

                $name = (!empty($route['name'])) ? $route['name'] : $controllerRoutes['base_route'] . '_' . RegexHelper::cameCaseToUnderscore($route['action']);
                $method = (!empty($route['method'])) ? $route['method'] : 'get';
                $routeObject = $controllers->match($route['route'], $fullCalledClassName . '::' . $route['action'] . 'Action')->method(strtoupper($method))->bind($name);

                //parameter options
                if(!empty($route['parameters']))
                {
                    foreach($route['parameters'] as $parameter => $options)
                    {
                        if(is_array($options) && array_key_exists('default', $options))
                        {
                            $routeObject->value($parameter, $options['default']);
                        }

                        if(is_array($options) && array_key_exists('regex', $options))
                        {
                            $routeObject->assert($parameter, $options['regex']);
                        }
                    }
                }
            }
        }

        if(!empty($autoRoutes))
        {
            foreach($autoRoutes as $actionName)
            {
                $name = $controllerRoutes['base_route'] . '_' . RegexHelper::cameCaseToUnderscore($actionName);
                $controllers->get(RegexHelper::cameCaseToUnderscore($actionName), $fullCalledClassName . '::' . $actionName . 'Action')->bind($name);
            }
        }

        return $controllers;
    }

    protected function addCssFile($path)
    {
        $this->cssFiles[] = $path;
    }

    protected function addJavascriptFile($path)
    {
        $this->javascriptFiles[] = $path;
    }

    protected function setRestFullModelName($value)
    {
        $this->restFullModelName = $value;
    }

    protected function resetRestFullModelName()
    {
        $this->restFullModelName = null;
    }

    protected function getRestFullModelName($exceptionOnEmpty = true)
    {
        if(empty($this->restFullModelName) && $exceptionOnEmpty)
        {
            throw new \Exception("Rest full model name must be provided");
        }

        return $this->restFullModelName;
    }

    protected function setRestObjectNameSingular($value)
    {
        $this->restObjectNameSingular = $value;
    }

    protected function resetRestObjectNameSingular()
    {
        $this->restObjectNameSingular = 'object';
    }

    protected function getRestObjectNameSingular($exceptionOnEmpty = true)
    {
        if(empty($this->restObjectNameSingular) && $exceptionOnEmpty)
        {
            throw new \Exception("Rest object singular name must be provided");
        }

        return $this->restObjectNameSingular;
    }

    protected function setRestObjectNamePlural($value)
    {
        $this->restObjectNamePlural = $value;
    }

    protected function resetRestObjectNamePlural()
    {
        $this->restObjectNamePlural = 'objects';
    }

    protected function getRestObjectNamePlural($exceptionOnEmpty = true)
    {
        if(empty($this->restObjectNamePlural) && $exceptionOnEmpty)
        {
            throw new \Exception("Rest object plural name must be provided");
        }

        return $this->restObjectNamePlural;
    }

    /**
     * Sets the override layout template
     *
     * @param string $templatePath The relative path to the override layout template
     *
     * @return void
     */
    protected function setOverrideLayoutTemplate($templatePath)
    {
        $this->overrideLayoutTemplate = $templatePath;
    }

    /**
     * Removes the override layout template path
     */
    protected function unsetOverrideLayoutTemplate()
    {
        $this->overrideLayoutTemplate = null;
    }

    /**
     * Helper method to render twig templates more dynamically
     *
     * @param array $data optional Data to pass to Twig
     * @param string|null $templatePathOverride optional Manually specify which template to use (without the .twig)
     *
     * @return string The rendered template
     */
    protected function renderTwig($data = array(), $templatePathOverride = null)
    {
        if(!is_array($data))
        {
            $data = array();
        }

        $data['global_layout_template'] = self::$globalLayoutTemplate;
        $data['layout_template'] = $this->getLayoutTemplate();
        $data['css_files'] = $this->cssFiles;
        $data['javascript_files'] = $this->javascriptFiles;
        $data['session'] = self::$session;

        if(empty($templatePathOverride))
        {
            $folder = RegexHelper::cameCaseToUnderscore(substr($this->getNonNamespacedCalledClass(), 0, -10));
            $calledMethod = $this->getCalledActionMethod();
            $templateName =  RegexHelper::cameCaseToUnderscore(substr($calledMethod, 0, -6));
            $templatePath = $folder . '/' . $templateName;
        }
        else
        {
            $templatePath = $templatePathOverride;
        }

        return self::$twig->render($templatePath . '.twig', $data);
    }

    /**
     * Simple wrapper for rendering json to the screen
     *
     * @param array $data
     * @return string
     */
    protected function renderJson($data = array())
    {
        return json_encode($data);
    }

    /**
     * Provide general read (both single and list)   rest functionality for all controllers that set the rest full model name
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param null|mixed $objectId
     * @param string $dataType
     *
     * @return mixed
     */
    public function restDataAction(Request $request, $objectId = null, $dataType = 'json')
    {
        $qualifiedObjectName = $this->getRestFullModelName();
        $data = array();

        if(!empty($objectId))
        {
            $object = new $qualifiedObjectName($objectId);
            $data[$this->getRestObjectNameSingular()] = $object->toArray();
        }
        else
        {
            $queryData = RegexHelper::arrayUnderscoreKeyToCameCaseKey($request->query->all());
            $limit = (!empty($queryData['limit']) && is_numeric($queryData['limit'])) ? $queryData['limit']  : null;
            $offset = (!empty($queryData['offset']) && is_numeric($queryData['offset'])) ? $queryData['offset']  : 0;
            $order = (!empty($queryData['order']) && is_array($queryData['order'])) ? $queryData['order']  : array();

            unset($queryData['limit'], $queryData['offset'], $queryData['order']);

            $objects = $qualifiedObjectName::get($queryData, array(), array(), $order, $limit, $offset);
            $data[$this->getRestObjectNamePlural()] = $objects->toArray();
            $data['total'] = $qualifiedObjectName::getCount($queryData);
            $data['page'] = (is_numeric($limit) && $offset % $limit == 0) ? floor((($offset + $limit) / $limit)) : null;
            $data['recordsPerPage'] = $limit;
        }

        $method = 'render' . ucfirst($dataType);
        return $this->$method(array('status' => 'success', 'data' => $data));
    }

    /**
     * Provide general create, update, and delete rest functionality for all controllers that set the rest full model name
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param null|mixed $objectId
     * @param string $dataType
     * @return mixed
     */
    public function restProcessDataAction(Request $request, $objectId = null, $dataType = 'json')
    {
        $objectName = $this->getRestFullModelName();
        $httpMethod = $request->getMethod();
        $user = new $objectName($objectId);
        $message = null;

        if($httpMethod == 'DELETE')
        {
            $user->delete();
            $status = 'success';
        }
        else
        {
            $data = RegexHelper::arrayUnderscoreKeyToCameCaseKey($request->request->all());
            $user->loadByArray($data, false, true);
            $user->save();
            $status = 'success';
        }

        $data = array
        (
            'object' => $user->toArray()
        );

        $method = 'render' . ucfirst($dataType);
        return $this->$method(array('status' => $status, 'data' => $data, 'message' => $message));
    }

    /**
     * Returns the path to the layout template that should be used
     *
     * @return string The path to the layout template to use
     */
    private function getLayoutTemplate()
    {
        return (!empty($this->overrideLayoutTemplate)) ? $this->overrideLayoutTemplate : self::$globalLayoutTemplate;
    }

    /**
     * Returns the called method from the parent controller
     *
     * @return string
     */
    private function getCalledActionMethod()
    {
        $trace = debug_backtrace();

        //[0] id this method and [1] in renderTwigMethod
        $x = 0;

        while(substr($trace[$x]['function'], -6) != 'Action' && $x < 10)
        {
            $x++;
        }

        if($x >= 10)
        {
            throw new \Exception('Could find action method that called renderTwig()');
        }

        return $trace[$x]['function'];
    }

    /**
     * Returns an array of action method names without the ending 'Action'
     *
     * @return array
     */
    private function generateActionRoutes()
    {
        $actions = array();
        $methods = get_class_methods(get_called_class());

        foreach($methods as $method)
        {
            if(substr($method, -6) == 'Action')
            {
                $actions[] = substr($method, 0, -6);
            }
        }

        return $actions;
    }

    /**
     * Returns the name of the called class without the namespace
     *
     * @return string
     */
    private function getNonNamespacedCalledClass()
    {
        return ClassHelper::getNonNamespacedClass(get_called_class());
    }
}
