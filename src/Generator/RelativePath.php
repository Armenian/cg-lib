<?php

declare(strict_types=1);

namespace CG\Generator;

class RelativePath
{
    private string $relativePath;

    public function __construct($relativePath)
    {
        $this->relativePath = $relativePath;
    }

    public function getRelativePath(): string
    {
        return $this->relativePath;
    }
}
