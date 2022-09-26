<?php

declare(strict_types=1);

namespace CG\Generator;

class PhpConstant
{
    private ?string $name;
    private ?string $value = null;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public static function create(string $name = null): PhpConstant
    {
        return new self($name);
    }

    public function setName(string $name): PhpConstant
    {
        $this->name = $name;
        return $this;
    }

    public function setValue(string $value): PhpConstant
    {
        $this->value = $value;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
