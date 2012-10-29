<?php
namespace SalvoTests;

require_once(__DIR__ . '/PdoTestCase.php');

use \Salvo\Barrage\DataSource\Relational\Driver\Mysql;
use Symfony\Component\Yaml\Yaml;
use \Salvo\Barrage\DataSource\Relational\IConnectionData;
use \Salvo\Barrage\DataSource\Relational\IDataSource;

abstract class BaseTestCase extends PdoTestCase
{
	public function __construct()
	{
	}
}
