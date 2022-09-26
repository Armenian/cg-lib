<?php

declare(strict_types=1);

namespace CG\Core;

use CG\Generator\DefaultVisitorInterface;
use CG\Generator\PhpClass;
use CG\Generator\DefaultVisitor;
use CG\Generator\DefaultNavigator;
use Closure;

/**
 * The default generator strategy.
 *
 * This strategy allows to change the order in which methods, properties and
 * constants are sorted.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultGeneratorStrategy implements GeneratorStrategyInterface
{

    private DefaultNavigator $navigator;
    private DefaultVisitorInterface $visitor;

    public function __construct(DefaultVisitorInterface $visitor = null)
    {
        $this->navigator = new DefaultNavigator();
        $this->visitor = $visitor ?: new DefaultVisitor();
    }

    public function setConstantSortFunc(Closure $func = null): void
    {
        $this->navigator->setConstantSortFunc($func);
    }

    public function setMethodSortFunc(Closure $func = null): void
    {
        $this->navigator->setMethodSortFunc($func);
    }

    public function setPropertySortFunc(Closure $func = null): void
    {
        $this->navigator->setPropertySortFunc($func);
    }

    public function generate(PhpClass $class): string
    {
        $this->visitor->reset();
        $this->navigator->accept($this->visitor, $class);

        return $this->visitor->getContent();
    }
}
