<?php

declare(strict_types=1);

namespace CG\Generator;

use CG\Core\ReflectionUtils;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Represents a PHP method.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpMethod extends AbstractPhpMember
{
    private bool $final = false;
    private bool $abstract = false;
    private array $parameters = [];
    private bool $referenceReturned = false;
    private ?string $returnType = null;
    private bool $returnTypeBuiltin = false;
    private string $body = '';
    private bool $nullAllowedForReturnType = false;

    /**
     * @param string|null $name
     * @return PhpMethod
     */
    public static function create(?string $name = null): PhpMethod
    {
        return new static($name);
    }

    /**
     * @param ReflectionMethod $ref
     * @return PhpMethod
     * @throws ReflectionException
     */
    public static function fromReflection(ReflectionMethod $ref): PhpMethod
    {
        $method = new static();
        $method
            ->setFinal($ref->isFinal())
            ->setAbstract($ref->isAbstract())
            ->setStatic($ref->isStatic())
            ->setVisibility(self::getVisibilityFromReflection($ref))
            ->setReferenceReturned($ref->returnsReference())
            ->setName($ref->name)
        ;

        if (method_exists($ref, 'getReturnType') && $type = $ref->getReturnType()) {
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
            } else {
                $typeName = (string)$type;
            }
            $method->setReturnType($typeName, $type->allowsNull());
        }

        if ($docComment = $ref->getDocComment()) {
            $method->setDocblock(ReflectionUtils::getUnindentedDocComment($docComment));
        }

        foreach ($ref->getParameters() as $param) {
            $method->addParameter(static::createParameter($param));
        }

        // FIXME: Extract body?
        return $method;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return PhpParameter
     * @throws ReflectionException
     */
    protected static function createParameter(ReflectionParameter $parameter): PhpParameter
    {
        return PhpParameter::fromReflection($parameter);
    }

    /**
     * @param ReflectionMethod $ref
     * @return string
     */
    public static function getVisibilityFromReflection(ReflectionMethod $ref): string
    {
        if ($ref->isPublic()) {
            return self::VISIBILITY_PUBLIC;
        }

        if ($ref->isProtected()) {
            return self::VISIBILITY_PROTECTED;
        }

        return self::VISIBILITY_PRIVATE;
    }

    /**
     * @param boolean $bool
     * @return PhpMethod
     */
    public function setFinal(bool $bool): PhpMethod
    {
        $this->final = $bool;
        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpMethod
     */
    public function setAbstract(bool $bool): PhpMethod
    {
        $this->abstract = $bool;
        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpMethod
     */
    public function setReferenceReturned(bool $bool): PhpMethod
    {
        $this->referenceReturned = $bool;
        return $this;
    }

    /**
     * @param string $body
     * @return PhpMethod
     */
    public function setBody(string $body): PhpMethod
    {
        $this->body = $body;

        return $this;
    }

    public function setParameters(array $parameters): PhpMethod
    {
        $this->parameters = array_values($parameters);

        return $this;
    }

    public function addParameter(PhpParameter $parameter): PhpMethod
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function setReturnType(string $type, $nullAllowed = false): PhpMethod
    {
        $this->returnType = $type;
        $this->returnTypeBuiltin = BuiltinType::isBuiltin($type);
        $this->nullAllowedForReturnType = $nullAllowed;
        return $this;
    }

    public function replaceParameter(int $position, PhpParameter $parameter): PhpMethod
    {
        if ($position < 0 || $position > count($this->parameters)) {
            throw new InvalidArgumentException(sprintf('The position must be in the range [0, %d].', count($this->parameters)));
        }
        $this->parameters[$position] = $parameter;

        return $this;
    }

    /**
     * @param integer $position
     * @return PhpMethod
     */
    public function removeParameter(int $position): PhpMethod
    {
        if (!isset($this->parameters[$position])) {
            throw new InvalidArgumentException(sprintf('There is no parameter at position "%d" does not exist.', $position));
        }
        unset($this->parameters[$position]);
        $this->parameters = array_values($this->parameters);

        return $this;
    }

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    public function isReferenceReturned(): bool
    {
        return $this->referenceReturned;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function hasReturnType(): bool
    {
        return null !== $this->getReturnType();
    }

    public function hasBuiltInReturnType(): bool
    {
        return $this->returnTypeBuiltin;
    }

    public function isNullAllowedForReturnType(): bool
    {
        return $this->nullAllowedForReturnType;
    }

    public function setName($name): PhpMethod
    {
        parent::setName($name);
        return $this;
    }

    public function setVisibility($visibility): PhpMethod
    {
        parent::setVisibility($visibility);
        return $this;
    }

    public function setStatic($bool): PhpMethod
    {
        parent::setStatic($bool);
        return $this;
    }

    public function setDocblock($doc): PhpMethod
    {
        parent::setDocblock($doc);
        return $this;
    }
}
