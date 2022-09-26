<?php

declare(strict_types=1);

namespace CG\Proxy;

use CG\Generator\PhpClass;
use ReflectionClass;

/**
 * Interface for enhancing generators.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * Generates the necessary changes in the class.
     *
     * @param ReflectionClass $originalClass
     * @param PhpClass $generatedClass The generated class
     * @return void
     */
    public function generate(ReflectionClass $originalClass, PhpClass $generatedClass): void;
}
