<?php
/**
 * This is part of the Salvo framework.
 *
 * (c) Ryan Zec <code@ryanzec.com>
 *
 * Licensed under MIT, see LICENSE file that came with source code
 */
namespace Barrage\Utility;

/**
 * Class to helper perform certain common regex actions
 *
 * @author Ryan Zec <code@ryanzec.com>
 */
class RegexHelper
{
    /**
     * Converts camelCase to lowercase_with_underscores
     *
     * @static
     *
     * @param $string
     *
     * @return string
     */
    public static function cameCaseToUnderscore($string)
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]]/','_\0', $string));
    }

    /**
     * Does it best job in converting a string into lowercase_with_underscores
     *
     * @static
     *
     * @param $string
     *
     * @return string
     */
    public static function toUnderscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/','$1_$2', $string));
    }

    /**
     * Convert a lowercase with underscores string to camelCase string
     *
     * @static
     *
     * @param $string
     *
     * @return string
     */
    public static function underscoreToCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    /**
     * Convert an array keyed by lowercase with underscores string to an array keyed by camelCase string
     *
     * @static
     *
     * @param $array
     *
     * @return array
     */
    public static function arrayUnderscoreKeyToCameCaseKey(array $array)
    {
        $camelCaseArray = array();

        if(!empty($array) && is_array($array))
        {
            foreach($array as $key => $value)
            {
                $camelCaseArray[self::underscoreToCamelCase($key)] = $value;
            }
        }

        return $camelCaseArray;
    }
}
