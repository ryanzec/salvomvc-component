<?php
namespace BarrageTests\Utility;

use Barrage\Utility\RegexHelper;
use BarrageTests\BaseTestCase;

/**
 * RegexHelper unit tests
 */
class RegexHelperTest extends BaseTestCase
{
    /**
     * @test
     */
    public function cameCaseToUnderscore()
    {
        $this->assertEquals('camel_case', RegexHelper::cameCaseToUnderscore('camelCase'));
    }

    /**
     * @test
     */
    public function toUnderscore()
    {
        $this->assertEquals('camel_case', RegexHelper::toUnderscore('camelCase'));
        $this->assertEquals('something_weird', RegexHelper::toUnderscore('SOMEthingWeird'));
        $this->assertEquals('this_is_not_good', RegexHelper::toUnderscore('thisIsNot_GOOD'));
    }

    /**
     * @test
     */
    public function underscoreToCamelCase()
    {
        $this->assertEquals('doSomeThingElse', RegexHelper::underscoreToCamelCase('do_some_thing_else'));
    }

    /**
     * @test
     */
    public function arrayUnderscoreKeyToCameCaseKey()
    {
        $underscoreKey = array
        (
            'do_some_thing_else' => 'test',
            'something_else' => 'test',
            'camel_case' => 'test'
        );
        $excepted = array
        (
            'doSomeThingElse' => 'test',
            'somethingElse' => 'test',
            'camelCase' => 'test'
        );
        $this->assertEquals($excepted, RegexHelper::arrayUnderscoreKeyToCameCaseKey($underscoreKey));
    }
}
