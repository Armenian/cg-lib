<?php

declare(strict_types=1);

namespace CG\Proxy;

use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * Represents a method invocation.
 *
 * This object contains information for the method invocation, such as the object
 * on which the method is invoked, and the arguments that are passed to the method.
 *
 * Before the actual method is called, first all the interceptors must call the
 * proceed() method on this class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class MethodInvocation
{
    public ReflectionMethod $reflection;
    public object $object;
    public array $arguments;

    private array $interceptors;
    private int $pointer;

    public function __construct(ReflectionMethod $reflection, $object, array $arguments, array $interceptors)
    {
        $this->reflection = $reflection;
        $this->object = $object;
        $this->arguments = $arguments;
        $this->interceptors = $interceptors;
        $this->pointer = 0;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getNamedArgument(string $name)
    {
        foreach ($this->reflection->getParameters() as $i => $param) {
            if ($param->name !== $name) {
                continue;
            }

            if ( ! array_key_exists($i, $this->arguments)) {
                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                throw new RuntimeException(sprintf('There was no value given for parameter "%s".', $param->name));
            }

            return $this->arguments[$i];
        }

        throw new InvalidArgumentException(sprintf('The parameter "%s" does not exist.', $name));
    }

    /**
     * Proceeds down the call-chain and eventually calls the original method.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function proceed()
    {
        if (isset($this->interceptors[$this->pointer])) {
            return $this->interceptors[$this->pointer++]->intercept($this);
        }

        $this->reflection->setAccessible(true);

        return $this->reflection->invokeArgs($this->object, $this->arguments);
    }

    /**
     * Returns a string representation of the method.
     *
     * This is intended for debugging purposes only.
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function __toString(): string
    {
        return sprintf('%s::%s', $this->reflection->class, $this->reflection->name);
    }
}
