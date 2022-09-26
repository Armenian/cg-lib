<?php

declare(strict_types=1);

namespace CG\Generator;

class BuiltinType
{
    private static array $builtinTypes = ['self', 'array', 'callable', 'bool', 'float', 'int', 'string', 'void', 'iterable', 'object'];

    public static function isBuiltin(string $type): bool
    {
        return in_array($type, static::$builtinTypes, true);
    }
}



