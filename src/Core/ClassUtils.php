<?php

declare(strict_types=1);

namespace CG\Core;

abstract class ClassUtils
{
    /**
     * @param string $className
     * @return false|string
     */
    public static function getUserClass(string $className)
    {
        if (false === $pos = strrpos($className, '\\'.NamingStrategyInterface::SEPARATOR.'\\')) {
            return $className;
        }

        return substr($className, $pos + NamingStrategyInterface::SEPARATOR_LENGTH + 2);
    }
}
