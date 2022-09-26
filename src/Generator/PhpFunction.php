<?php

declare(strict_types=1);

namespace CG\Generator;

/**
 * Represents a PHP function.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
use CG\Core\ReflectionUtils;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

class PhpFunction
{
    private ?string $name;
    private ?string $namespace = null;
    private array $parameters = [];
    private string $body = '';
    private bool $referenceReturned = false;
    private ?string $docblock = null;
    private ?string $returnType = null;
    private bool $returnTypeBuiltin = false;

    /**
     * @param ReflectionFunction $ref
     * @return PhpFunction
     */
    public static function fromReflection(ReflectionFunction $ref): PhpFunction
    {
        $function = new static();

        if (false === $pos = strrpos($ref->name, '\\')) {
            $function->setName(substr($ref->name, $pos + 1));
            $function->setNamespace(substr($ref->name, $pos));
        } else {
            $function->setName($ref->name);
        }

        if (method_exists($ref, 'getReturnType') && $type = $ref->getReturnType()) {
            $function->setReturnType((string)$type);
        }
        $function->referenceReturned = $ref->returnsReference();
        $function->docblock = ReflectionUtils::getUnindentedDocComment($ref->getDocComment());

        foreach ($ref->getParameters() as $refParam) {
            assert($refParam instanceof ReflectionParameter);

            $param = PhpParameter::fromReflection($refParam);
            $function->addParameter($param);
        }

        return $function;
    }

    public static function create(?string $name = null): PhpFunction
    {
        return new static($name);
    }

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return PhpFunction
     */
    public function setName(string $name): PhpFunction
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $namespace
     * @return PhpFunction
     */
    public function setNamespace(string $namespace): PhpFunction
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * In contrast to getName(), this method accepts the fully qualified name
     * including the namespace.
     *
     * @param string $name
     * @return PhpFunction
     */
    public function setQualifiedName(string $name): PhpFunction
    {
        if (false !== $pos = strrpos($name, '\\')) {
            $this->namespace = substr($name, 0, $pos);
            $this->name = substr($name, $pos + 1);

            return $this;
        }

        $this->namespace = null;
        $this->name = $name;

        return $this;
    }

    public function setParameters(array $parameters): PhpFunction
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return PhpFunction
     */
    public function setReferenceReturned(bool $bool): PhpFunction
    {
        $this->referenceReturned = $bool;

        return $this;
    }

    public function setReturnType(string $type): PhpFunction
    {
        $this->returnType = $type;
        $this->returnTypeBuiltin = BuiltinType::isBuiltIn($type);
        return $this;
    }

    /**
     * @param integer $position
     * @param PhpParameter $parameter
     * @return PhpFunction
     */
    public function replaceParameter(int $position, PhpParameter $parameter): PhpFunction
    {
        if ($position < 0 || $position > count($this->parameters)) {
            throw new InvalidArgumentException(sprintf('$position must be in the range [0, %d].', count($this->parameters)));
        }

        $this->parameters[$position] = $parameter;

        return $this;
    }

    public function addParameter(PhpParameter $parameter): PhpFunction
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    /**
     * @param integer $position
     * @return PhpFunction
     */
    public function removeParameter(int $position): PhpFunction
    {
        if (!isset($this->parameters[$position])) {
            throw new InvalidArgumentException(sprintf('There is not parameter at position %d.', $position));
        }

        unset($this->parameters[$position]);
        $this->parameters = array_values($this->parameters);

        return $this;
    }

    /**
     * @param string $body
     * @return PhpFunction
     */
    public function setBody(string $body): PhpFunction
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param string $docBlock
     * @return PhpFunction
     */
    public function setDocblock(string $docBlock): PhpFunction
    {
        $this->docblock = $docBlock;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getQualifiedName(): ?string
    {
        if ($this->namespace) {
            return $this->namespace.'\\'.$this->name;
        }

        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getDocblock(): ?string
    {
        return $this->docblock;
    }

    public function isReferenceReturned(): bool
    {
        return $this->referenceReturned;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function hasReturnType(): bool
    {
        return null !== $this->getReturnType();
    }

    public function hasBuiltinReturnType(): bool
    {
        return $this->returnTypeBuiltin;
    }

}
