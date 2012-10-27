<?php
namespace Salvo\Utility;

class RouteHelper
{
    public static function parametrize($routeObject, $parameters = array())
    {
        //parameter options
        if(!empty($parameters))
        {
            foreach($parameters as $parameter => $options)
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
