<?php
namespace SalvoTests;

require_once(__DIR__ . '/PdoTestCase.php');

use \Barrage\DataSource\Relational\Driver\Mysql;
use Symfony\Component\Yaml\Yaml;
use \Barrage\DataSource\Relational\IConnectionData;
use \Barrage\DataSource\Relational\IDataSource;

abstract class BaseTestCase extends PdoTestCase
{
    public function __construct()
    {
    }
}
