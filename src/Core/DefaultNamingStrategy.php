<?php

declare(strict_types=1);

namespace CG\Core;

use ReflectionClass;

/**
 * The default naming strategy.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultNamingStrategy implements NamingStrategyInterface
{

    public function __construct(private string $prefix = 'EnhancedProxy')
    {}

    public function getClassName(ReflectionClass $class): string
    {
        $userClass = ClassUtils::getUserClass($class->name);

        return $this->prefix.'_'.sha1($class->name).'\\'.self::SEPARATOR.'\\'.$userClass;
    }
}
