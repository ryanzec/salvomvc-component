<?php
namespace Salvo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MonologServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        /*if (isset($app['monolog.class_path'])) {
            $app['autoloader']->registerNamespace('Monolog', $app['monolog.class_path']);
        }*/
    }
}
