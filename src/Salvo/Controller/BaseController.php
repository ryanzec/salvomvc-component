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
use Salvo\Utility\RouteHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

	private $httpStatusCode = 200;

	/**
	 * The name full name of the model to be used with the built-in rest functionality to provide basic create, read, update, delete and listing functionality
	 *
	 * @var null|string
	 */
	private $restFullModelName = null;

	private $restObjectNameSingular = 'data';

	private $restObjectNamePlural = 'data';

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
		$controllers = $application['controllers_factory'];

		//get information needed to dynamically configure routes
		$fullCalledClassName = get_called_class();
		$baseCalledClassName = $this->getNonNamespacedCalledClass();

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
				RouteHelper::parametrize($routeObject, $route['parameters']);
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
		$this->restObjectNameSingular = 'data';
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
		$this->restObjectNamePlural = 'data';
	}

	protected function getRestObjectNamePlural($exceptionOnEmpty = true)
	{
		if(empty($this->restObjectNamePlural) && $exceptionOnEmpty)
		{
			throw new \Exception("Rest object plural name must be provided");
		}

		return $this->restObjectNamePlural;
	}

	protected function setHttpStatusCode($value)
	{
		$this->httpStatusCode = $value;
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

	private function renderResponse($content)
	{
		return new Response($content, $this->httpStatusCode);
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

		$calledMethod = $this->getCalledActionMethod();
		$htmlPageIdentifier = RegexHelper::cameCaseToUnderscore(substr($this->getNonNamespacedCalledClass(), 0, -10) . ucfirst(substr($calledMethod, 0, -6)));
		$htmlPageIdentifier = str_replace('_', '-', $htmlPageIdentifier);

		$data['globalLayoutTemplate'] = self::$globalLayoutTemplate;
		$data['layoutTemplate'] = $this->getLayoutTemplate();
		$data['cssFiles'] = $this->cssFiles;
		$data['javascriptFiles'] = $this->javascriptFiles;
		$data['session'] = self::$session;
		$data['htmlPageIdentifier'] = $htmlPageIdentifier;

		if(empty($templatePathOverride))
		{
			$folder = RegexHelper::cameCaseToUnderscore(substr($this->getNonNamespacedCalledClass(), 0, -10));
			$templateName =  RegexHelper::cameCaseToUnderscore(substr($calledMethod, 0, -6));
			$templatePath = $folder . '/' . $templateName;
		}
		else
		{
			$templatePath = $templatePathOverride;
		}

		return $this->renderResponse(self::$twig->render($templatePath . '.twig', $data));
	}

	/**
	 * Simple wrapper for rendering json to the screen
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	protected function renderJson($data = array(), $jsonpCallback = null)
	{
		if(!empty($jsonpCallback)) {
			$content = $jsonpCallback . '(' . json_encode($data) . ')';
		} else {
			$content = json_encode($data);
		}

		return $this->renderResponse($content);
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
			//todo: remove passing in the $request parameter manually
			$data = $this->restData($request);
			$jsonpCallback = false;

			$queryData = RegexHelper::arrayUnderscoreKeyToCameCaseKey($data['queryData']);

			if($queryData['callback']) {
				$jsonpCallback = $queryData['callback'];
				unset($queryData['callback']);
			}

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

		if(!empty($jsonpCallback) && $method === 'renderJson') {
			return $this->$method(array('status' => 'success', 'data' => $data), $jsonpCallback);
		} else {
			return $this->$method(array('status' => 'success', 'data' => $data));
		}
	}

	protected function restData(Request $request)
	{
		$return = array
		(
			'queryData' => array()
		);

		//since this data comes from the url, we have to assume it is url encoded
		$queryData = $request->query->all();
		$count = count($queryData);

		if($count > 0)
		{
			foreach($queryData as $key => $value)
			{
				if(!is_array($value)) {
					$queryData[$key] = urldecode($value);
				} else {
					$queryData[$key] = array();

					foreach($value as $k => $v) {
						$queryData[$key][$k] = urldecode($v);
					}
				}
			}

			$return['queryData'] = $queryData;
		}

		return $return;
	}

	/**
	 * Provide general create, update, and delete rest functionality for all controllers that set the rest full model name
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param null|mixed $objectId
	 * @param string $dataType
	 *
	 * @throws \Exception
	 *
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
			if(!$user instanceof $objectName)
			{
				throw new \Exception('Unable to load record for deletion');
			}

			$user->delete();
			$status = 'success';
		}
		else
		{
			$parsedData = $this->restProcessData($request);
			$data = RegexHelper::arrayUnderscoreKeyToCameCaseKey($parsedData['contentData']);

			$user->loadByArray($data, false);
			$user->save();
			$status = 'success';
		}

		$data = array
		(
			$this->getRestObjectNameSingular() => ($httpMethod !== 'DELETE') ? $user->toArray() : array()
		);

		$method = 'render' . ucfirst($dataType);
		return $this->$method(array('status' => $status, 'data' => $data, 'message' => $message));
	}

	protected function restProcessData(Request $request)
	{
		$return = array
		(
			'contentData' => array()
		);

		//default implementation request data for post/put
		$requestContent = $request->getContent();

		if(!empty($requestContent))
		{
			$jsonData = json_decode($requestContent, true);

			//default implementation request rest calls to pass data as a json string
			if(!is_array($jsonData))
			{
				throw new \Exception("");
			}

			$return['contentData'] = $jsonData;
		}

		return $return;
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
	 * @throws \Exception
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
