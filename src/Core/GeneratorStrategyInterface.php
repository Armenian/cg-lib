<?php

declare(strict_types=1);

namespace CG\Core;

use CG\Generator\PhpClass;

/**
 * Generator Strategy Interface.
 *
 * Implementing classes are responsible for generating PHP code from the given
 * PhpClass instance.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface GeneratorStrategyInterface
{
    public function generate(PhpClass $class): string;
}
