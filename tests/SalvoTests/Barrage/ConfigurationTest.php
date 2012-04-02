<?php
namespace SalvoTests\Barrage;

class ConfigurationTest extends BaseTestCase
{
    /**
     * @test
     */
    public function getRealDatabaseName()
    {
        $this->assertEquals('TrueTest2', \Salvo\Barrage\Configuration::getRealDatabaseName('TrueTest'));
    }

    /**
     * @test
     */
    public function getTrueDatabaseName()
    {
        $this->assertEquals('TrueTest', \Salvo\Barrage\Configuration::getTrueDatabaseName('TrueTest2'));
    }
}
