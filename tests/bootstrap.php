<?php
define('UNIT_TESTING', true);

require_once(__DIR__ . '/../../../.composer/autoload.php');
//require_once(__DIR__ . '/../../../../bootstrap.php');

function myAutoload($name)
{
    if(strpos($name, 'BarrageTests') !== false)
    {
        $path = str_replace("\\", '/', str_replace('BarrageTests\\', '', $name));
        require_once(__DIR__ . '/BarrageTests/' . $path . '.php');
    }
    else if(strpos($name, 'Barrage') !== false)
    {
        $path = str_replace("\\", '/', $name);
        require_once(__DIR__ . '/../src/' . $path . '.php');
    }
    else if(strpos($name, 'SalvoTests') !== false)
    {
        $path = str_replace("\\", '/', str_replace('SalvoTests\\', '', $name));
        require_once(__DIR__ . '/SalvoTests/' . $path . '.php');
    }
    else if(strpos($name, 'Salvo') !== false)
    {
        $path = str_replace("\\", '/', $name);
        require_once(__DIR__ . '/../src/' . $path . '.php');
    }
}

spl_autoload_register('myAutoload');
