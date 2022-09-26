<?php

declare(strict_types=1);

namespace CG\Core;

use ReflectionClass;

/**
 * The naming strategy interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface NamingStrategyInterface
{
    public const SEPARATOR = '__CG__';
    public const SEPARATOR_LENGTH = 6;

    /**
     * Returns the class name for the proxy class.
     *
     * The generated class name MUST be the concatenation of a nonempty prefix,
     * the namespace separator __CG__, and the original class name.
     *
     * Examples:
     *
     *    +----------------------------+------------------------------+
     *    | Original Name              | Generated Name               |
     *    +============================+==============================+
     *    | Foo\Bar                    | dred332\__CG__\Foo\Bar       |
     *    | Bar\Baz                    | Foo\Doo\__CG__\Bar\Baz       |
     *    +----------------------------+------------------------------+
     *
     * @param ReflectionClass $class
     * @return string the class name for the generated class
     */
    public function getClassName(ReflectionClass $class): string;
}
