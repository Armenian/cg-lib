<?php

declare(strict_types=1);

namespace CG\Proxy;

use CG\Generator\Writer;
use CG\Core\ReflectionUtils;
use CG\Generator\GeneratorUtils;
use CG\Generator\PhpParameter;
use CG\Generator\PhpMethod;
use CG\Generator\PhpProperty;
use CG\Generator\PhpClass;
use ReflectionClass;
use ReflectionException;

/**
 * Generator for creating lazy-initializing instances.
 *
 * This generator enhances concrete classes to allow for them to be lazily
 * initialized upon first access.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class LazyInitializerGenerator implements GeneratorInterface
{
    private Writer $writer;
    private string $prefix = '__CG__';

    public function __construct()
    {
        $this->writer = new Writer();
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Generates the necessary methods in the class.
     *
     * @param ReflectionClass $originalClass
     * @param PhpClass $class
     * @return void
     * @throws ReflectionException
     */
    public function generate(ReflectionClass $originalClass, PhpClass $class): void
    {
        $methods = ReflectionUtils::getOverrideableMethods($originalClass, true);

        // no public, non final methods
        if (empty($methods)) {
            return;
        }

        $initializer = new PhpProperty();
        $initializer->setName($this->prefix.'lazyInitializer');
        $initializer->setVisibility(PhpProperty::VISIBILITY_PRIVATE);
        $class->setProperty($initializer);

        $initialized = new PhpProperty();
        $initialized->setName($this->prefix.'initialized');
        $initialized->setDefaultValue(false);
        $initialized->setVisibility(PhpProperty::VISIBILITY_PRIVATE);
        $class->setProperty($initialized);

        $initializerSetter = new PhpMethod();
        $initializerSetter->setName($this->prefix.'setLazyInitializer');
        $initializerSetter->setBody('$this->'.$this->prefix.'lazyInitializer = $initializer;');

        $parameter = new PhpParameter();
        $parameter->setName('initializer');
        $parameter->setType(LazyInitializerInterface::class);
        $initializerSetter->addParameter($parameter);
        $class->setMethod($initializerSetter);

        $this->addMethods($class, $methods);

        $initializingMethod = new PhpMethod();
        $initializingMethod->setName($this->prefix.'initialize');
        $initializingMethod->setVisibility(PhpMethod::VISIBILITY_PRIVATE);
        $initializingMethod->setBody(
            $this->writer
                ->reset()
                ->writeln('if (null === $this->'.$this->prefix.'lazyInitializer) {')
                    ->indent()
                    ->writeln('throw new \RuntimeException("'.$this->prefix.'setLazyInitializer() must be called prior to any other public method on this object.");')
                    ->outdent()
                ->write("}\n\n")
                ->writeln('$this->'.$this->prefix.'lazyInitializer->initializeObject($this);')
                ->writeln('$this->'.$this->prefix.'initialized = true;')
                ->getContent()
        );
        $class->setMethod($initializingMethod);
    }

    /**
     * @param PhpClass $class
     * @param array $methods
     * @throws ReflectionException
     */
    private function addMethods(PhpClass $class, array $methods): void
    {
        foreach ($methods as $method) {
            $initializingCode = 'if (false === $this->'.$this->prefix.'initialized) {'."\n"
            .'    $this->'.$this->prefix.'initialize();'."\n"
            .'}';

            if ($class->hasMethod($method->name)) {
                $genMethod = $class->getMethod($method->name);
                $genMethod->setBody(
                $initializingCode."\n"
                .$genMethod->getBody()
                );

                continue;
            }

            $genMethod = PhpMethod::fromReflection($method);
            $genMethod->setBody(
            $initializingCode."\n\n"
            .'return '.GeneratorUtils::callMethod($method).';'
            );
            $class->setMethod($genMethod);
        }
    }
}
