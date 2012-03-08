<?php
namespace BarrageTests\DataSource\Relational;

use Barrage\DataSource\Relational\ConnectionDataFactory;
use Barrage\DataSource\Relational\Driver\Mysql;

class ConnectionDataFactoyTest extends \BarrageTests\BaseTestCase
{
    /**
     * @test
     */
    public function buildFromConfigurationMysql()
    {
        $configurationName = 'default';
        $connectionData = ConnectionDataFactory::buildFromConfiguration($configurationName);

        $this->assertSame(true, $connectionData instanceof Mysql\ConnectionData);
        $this->assertEquals('mysql', $connectionData->getDefaultDatabase());
        $this->assertEquals(array(), $connectionData->getOptions());
        $this->assertEquals('root', $connectionData->getUsername());
        $this->assertEquals('password', $connectionData->getPassword());
        $this->assertEquals('mysql:dbname=mysql;host=127.0.0.1;port=3306', $connectionData->getConnectionString());
    }

    /**
     * @test
     */
    public function buildFromConfigurationMysqlWithcustom_portMysql()
    {
        $configurationName = 'custom_port';
        $connectionData = ConnectionDataFactory::buildFromConfiguration($configurationName);

        $this->assertSame(true, $connectionData instanceof Mysql\ConnectionData);
        $this->assertEquals('mysql', $connectionData->getDefaultDatabase());
        $this->assertEquals(array(), $connectionData->getOptions());
        $this->assertEquals('root', $connectionData->getUsername());
        $this->assertEquals('password', $connectionData->getPassword());
        $this->assertEquals('mysql:dbname=mysql;host=127.0.0.1;port=1234', $connectionData->getConnectionString());
    }

    /**
     * @test
     */
    public function buildFromConfigurationMysqlWithcustom_portOptionsMysql()
    {
        $configurationName = 'with_options';
        $connectionData = ConnectionDataFactory::buildFromConfiguration($configurationName);

        $this->assertSame(true, $connectionData instanceof Mysql\ConnectionData);
        $this->assertEquals('mysql', $connectionData->getDefaultDatabase());
        $this->assertEquals(array(\PDO::MYSQL_ATTR_READ_DEFAULT_FILE => '/etc/test'), $connectionData->getOptions());
        $this->assertEquals('root', $connectionData->getUsername());
        $this->assertEquals('password', $connectionData->getPassword());
        $this->assertEquals('mysql:dbname=mysql;host=127.0.0.1;port=1235', $connectionData->getConnectionString());
    }
}
