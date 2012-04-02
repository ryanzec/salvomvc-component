<?php
namespace SalvoTests\Barrage;

require_once(__DIR__ . '/PdoTestCase.php');

use Salvo\Barrage\DataSource\Relational\Driver\Mysql;
use Symfony\Component\Yaml\Yaml;
use \Salvo\Barrage\DataSource\Relational\IConnectionData;
use \Salvo\Barrage\DataSource\Relational\IDataSource;

abstract class BaseTestCase extends PdoTestCase
{
    public function __construct()
    {
        $host = '127.0.0.1';
        $username = 'root';
        $password = '';
        $database = '';

        $testDatabaseConnection = Mysql\DataSource::getInstance(new Mysql\ConnectionData($host, $username, $password, $database));
        parent::__construct($testDatabaseConnection->getPdoConnection());

        \Salvo\Barrage\Configuration::load(__DIR__ . '/../../configuration.yml');
    }
}
