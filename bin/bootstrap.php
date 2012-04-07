<?php
spl_autoload_register(function($name)
{
    if(strpos($name, 'Barrage') !== false)
    {
        $path = str_replace("\\", '/', $name);
        require_once(__DIR__ . '/../src/' . $path . '.php');
    }
});

$configurationFilePath = dirname(__FILE__) . '/barrage.yml';
$consoleConfigurationFilePath = dirname(__FILE__) . '/barrageConsole.yml';

//load normal configurations
\Barrage\Configuration::load($configurationFilePath);

//load console specific configurations
\Barrage\Configuration::load($consoleConfigurationFilePath);
