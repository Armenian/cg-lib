<?php

declare(strict_types=1);

namespace CG\Generator;

use ReflectionMethod;

/**
 * Some Generator utils.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class GeneratorUtils
{
    public static function callMethod(ReflectionMethod $method, array $params = null): string
    {
        if (null === $params) {
            $params = array_map(static function($p) { return '$'.$p->name; }, $method->getParameters());
        }

        return '\\'.$method->getDeclaringClass()->name.'::'.$method->name.'('.implode(', ', $params).')';
    }
}
