<?php
namespace SalvoTests\Utility;

use Salvo\Utility\RegexHelper;
use SalvoTests\BaseTestCase;

/**
 * Console test suite
 *
 * @todo
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
}
