<?php

declare(strict_types=1);

namespace CG\Generator;

use InvalidArgumentException;

/**
 * Abstract PHP member class.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class AbstractPhpMember
{
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_PROTECTED = 'protected';
    public const VISIBILITY_PUBLIC = 'public';

    private bool $static = false;
    private string $visibility = self::VISIBILITY_PUBLIC;
    private ?string $name;
    private ?string $docblock = null;

    public function __construct(?string $name = null)
    {
        $this->setName($name);
    }

    /**
     * @param string|null $name
     * @return AbstractPhpMember
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $visibility
     * @return AbstractPhpMember
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setVisibility(string $visibility): self
    {
        if ($visibility !== self::VISIBILITY_PRIVATE
            && $visibility !== self::VISIBILITY_PROTECTED
            && $visibility !== self::VISIBILITY_PUBLIC) {
            throw new InvalidArgumentException(sprintf('The visibility "%s" does not exist.', $visibility));
        }

        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @param boolean $bool
     * @return AbstractPhpMember
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setStatic(bool $bool): self
    {
        $this->static = $bool;
        return $this;
    }

    /**
     * @param string|null $doc
     * @return AbstractPhpMember
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setDocblock(?string $doc): self
    {
        $this->docblock = $doc;
        return $this;
    }

    public function isStatic(): bool
    {
        return $this->static;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDocblock(): ?string
    {
        return $this->docblock;
    }
}
