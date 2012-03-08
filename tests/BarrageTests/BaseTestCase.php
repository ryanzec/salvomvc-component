<?php
namespace BarrageTests;

require_once(__DIR__ . '/PdoTestCase.php');

use Barrage\DataSource\Relational\Driver\Mysql;
use Symfony\Component\Yaml\Yaml;
use \Barrage\DataSource\Relational\IConnectionData;
use \Barrage\DataSource\Relational\IDataSource;

abstract class BaseTestCase extends PdoTestCase
{
    public function __construct()
    {
        $host = '127.0.0.1';
        $username = 'root';
        $password = 'password';
        $database = '';

        $testDatabaseConnection = Mysql\DataSource::getInstance(new Mysql\ConnectionData($host, $username, $password, $database));
        parent::__construct($testDatabaseConnection->getPdoConnection());

        \Barrage\Configuration::load(__DIR__ . '/../configuration.yml');
    }
}
