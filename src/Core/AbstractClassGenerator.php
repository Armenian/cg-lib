<?php

declare(strict_types=1);

namespace CG\Core;

use CG\Generator\PhpClass;
use ReflectionClass;

/**
 * Abstract base class for all class generators.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class AbstractClassGenerator
{

    private ?NamingStrategyInterface $namingStrategy = null;
    private ?GeneratorStrategyInterface $generatorStrategy = null;

    public function setNamingStrategy(NamingStrategyInterface $namingStrategy): void
    {
        $this->namingStrategy = $namingStrategy;
    }

    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy): void
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    public function getClassName(ReflectionClass $class): string
    {
        if (null === $this->namingStrategy) {
            $this->namingStrategy = new DefaultNamingStrategy();
        }

        return $this->namingStrategy->getClassName($class);
    }

    protected function generateCode(PhpClass $class): string
    {
        if (null === $this->generatorStrategy) {
            $this->generatorStrategy = new DefaultGeneratorStrategy();
        }

        return $this->generatorStrategy->generate($class);
    }
}
