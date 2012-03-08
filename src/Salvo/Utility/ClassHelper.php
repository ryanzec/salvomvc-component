<?php
namespace Salvo\Utility;

class ClassHelper
{
    public static function getNonNamespacedClass($className)
    {
        $classNameParts = explode('\\', $className);
        return end($classNameParts);
    }
}
