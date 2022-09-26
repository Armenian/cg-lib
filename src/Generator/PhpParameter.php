<?php

declare(strict_types=1);

namespace CG\Generator;

use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Represents a PHP parameter.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpParameter
{
    private ?string $name;
    private $defaultValue;
    private bool $hasDefaultValue = false;
    private bool $passedByReference = false;
    private ?string $type = null;
    private bool $typeBuiltin;

    /**
     * @param string|null $name
     * @return PhpParameter
     */
    public static function create(?string $name = null): PhpParameter
    {
        return new static($name);
    }

    /**
     * @param ReflectionParameter $ref
     * @return PhpParameter
     */
    public static function fromReflection(ReflectionParameter $ref): PhpParameter
    {
        $parameter = new static();
        $parameter
            ->setName($ref->name)
            ->setPassedByReference($ref->isPassedByReference())
        ;

        if ($ref->isDefaultValueAvailable()) {
            $parameter->setDefaultValue($ref->getDefaultValue());
        }

        if (method_exists($ref, 'getType')) {
            if ($type = $ref->getType()) {
                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                } else {
                    $typeName = (string)$type;
                }
                $parameter->setType($typeName);
            }
        } else if ($ref->isArray()) {
            $parameter->setType('array');
        } elseif ($class = $ref->getClass()) {
            $parameter->setType($class->name);
        } elseif (method_exists($ref, 'isCallable') && $ref->isCallable()) {
            $parameter->setType('callable');
        }

        return $parameter;
    }

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return PhpParameter
     */
    public function setName(string $name): PhpParameter
    {
        $this->name = $name;

        return $this;
    }

    public function setDefaultValue($value): PhpParameter
    {
        $this->defaultValue = $value;
        $this->hasDefaultValue = true;

        return $this;
    }

    public function unsetDefaultValue(): PhpParameter
    {
        $this->defaultValue = null;
        $this->hasDefaultValue = false;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpParameter
     */
    public function setPassedByReference(bool $bool): PhpParameter
    {
        $this->passedByReference = $bool;

        return $this;
    }

    /**
     * @param string $type
     * @return PhpParameter
     */
    public function setType(string $type): PhpParameter
    {
        $this->type = $type;
        $this->typeBuiltin = BuiltinType::isBuiltIn($type);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function isPassedByReference(): bool
    {
        return $this->passedByReference;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function hasType(): bool
    {
        return null !== $this->type;
    }

    public function hasBuiltinType(): bool
    {
        return $this->typeBuiltin;
    }
}
