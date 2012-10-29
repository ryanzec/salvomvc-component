<?php
namespace SalvoTests\Barrage\DataSource\Relational;

use Salvo\Barrage\DataSource\Relational\DataSourceFactory;
use Salvo\Barrage\DataSource\Relational\Driver\Mysql;

class DataSourceFactoryTest extends \SalvoTests\Barrage\BaseTestCase
{
	private $database1 = 'ut_barrage';

	/**
	 * This function is used to setup and tear down the database for the tests in this class
	 * Override in the child child class to load data into the database
	 *
	 * @return string The path to the data file to load for these tests
	 */
	public function getDataFileLocations()
	{
		return array(__DIR__ . '/yml/DataSourceFactoryTest.yml');
	}

	public function getSchema()
	{
		return array
		(
			'ut_barrage' => array
			(
				'types'
			)
		);
	}

	/**
	 * @test
	 */
	public function buildFromConfigurationMysql()
	{
		$configurationName = 'data_source_factory_test';
		$dataSource = DataSourceFactory::buildFromConfiguration($configurationName);

		$this->assertEquals('mysql', $dataSource->getServerVendorName());

		$sql = "SELECT * FROM {$this->database1}.types";
		$results = $dataSource->getAll($sql);

		$excepted = array
		(
			array
			(
				'id' => '1',
				'title' => 'none',
				'global' => '1',
				'enum' => null,
				'set' => null
			),
			array
			(
				'id' => '2',
				'title' => 'some',
				'global' => '0',
				'enum' => null,
				'set' => null
			)
		);

		$this->assertEquals($excepted, $results);
	}
}
