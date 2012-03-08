<?php
namespace Salvo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class YamlServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        /*if (isset($app['class_path'])) {
            $app['autoloader']->registerNamespace('Symfony', $app['class_path']);
        }*/
    }
}
