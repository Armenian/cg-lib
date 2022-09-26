<?php

declare(strict_types=1);

namespace CG\Proxy;

use CG\Core\AbstractClassGenerator;
use CG\Core\NamingStrategyInterface;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;
use CG\Generator\Writer;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Class enhancing generator implementation.
 *
 * This class enhances existing classes by generating a proxy and leveraging
 * different generator implementation.
 *
 * There are several built-in generator such as lazy-initializing objects, or
 * a generator for creating AOP joinpoints.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Enhancer extends AbstractClassGenerator
{

    private PhpClass $generatedClass;
    private ReflectionClass $class;
    /**
     * @var array|string[]
     */
    private array $interfaces;
    /**
     * @var array|InterceptionGenerator[]
     */
    private array $generators;

    public function __construct(ReflectionClass $class, array $interfaces = [], array $generators = [])
    {
        if (empty($generators) && empty($interfaces)) {
            throw new RuntimeException('Either generators, or interfaces must be given.');
        }

        $this->class = $class;
        $this->interfaces = $interfaces;
        $this->generators = $generators;
    }

    /**
     * Creates a new instance  of the enhanced class.
     *
     * @param array $args
     * @return object|null
     * @throws ReflectionException
     */
    public function createInstance(array $args = []): ?object
    {
        $generatedClass = $this->getClassName($this->class);

        if (!class_exists($generatedClass, false)) {
            eval($this->generateClass());
        }

        $ref = new ReflectionClass($generatedClass);

        return $ref->newInstanceArgs($args);
    }

    /**
     * @param string $filename
     * @throws ReflectionException
     */
    public function writeClass(string $filename): void
    {
        if (!is_dir($dir = dirname($filename)) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Could not create directory "%s".', $dir));
        }

        if (!is_writable($dir)) {
            throw new RuntimeException(sprintf('The directory "%s" is not writable.', $dir));
        }

        file_put_contents($filename, "<?php\n\n" . $this->generateClass());
    }

    /**
     * Creates a new enhanced class
     *
     * @return string
     * @throws ReflectionException
     */
    final public function generateClass(): string
    {
        static $docBlock;
        if (empty($docBlock)) {
            $writer = new Writer();
            $writer
                ->writeln('/**')
                ->writeln(' * CG library enhanced proxy class.')
                ->writeln(' *')
                ->writeln(' * This code was generated automatically by the CG library, manual changes to it')
                ->writeln(' * will be lost upon next generation.')
                ->write(' */');
            $docBlock = $writer->getContent();
        }

        $this->generatedClass = PhpClass::create()
            ->setDocblock($docBlock)
            ->setParentClassName($this->class->name);

        $proxyClassName = $this->getClassName($this->class);
        if (false === strpos($proxyClassName, NamingStrategyInterface::SEPARATOR)) {
            throw new RuntimeException(sprintf('The proxy class name must be suffixed with "%s" and an optional string, but got "%s".', NamingStrategyInterface::SEPARATOR, $proxyClassName));
        }
        $this->generatedClass->setName($proxyClassName);

        if (!empty($this->interfaces)) {
            $this->generatedClass->setInterfaceNames(array_map(static function ($v) {
                return '\\' . $v;
            }, $this->interfaces));

            foreach ($this->getInterfaceMethods() as $method) {
                $method = PhpMethod::fromReflection($method);
                $method->setAbstract(false);

                $this->generatedClass->setMethod($method);
            }
        }

        if (!empty($this->generators)) {
            foreach ($this->generators as $generator) {
                $generator->generate($this->class, $this->generatedClass);
            }
        }

        return $this->generateCode($this->generatedClass);
    }

    /**
     * Adds stub methods for the interfaces that have been implemented.
     */
    protected function getInterfaceMethods(): array
    {
        return array_merge(...array_map(static function(string $interface) {
            return (new ReflectionClass($interface))->getMethods();
        }, $this->interfaces));
    }
}
