<?php
namespace Salvo\Extension;

class Twig extends \Twig_Extension
{
    private static $silexApplication;

    public function __construct(\Silex\Application $application)
    {
        self::$silexApplication = $application;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    function getName()
    {
        return 'salvo_twig_extension';
    }

    public function getFunctions()
    {
        return array
        (
            'route_url_generator' => new \Twig_Function_Function('\Salvo\Extension\Twig::routeUrlGenerator')
        );
    }

    public static function routeUrlGenerator($routeName, $parameters = array())
    {
        return self::$silexApplication['url_generator']->generate($routeName, $parameters);
    }
}
