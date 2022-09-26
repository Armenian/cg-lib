<?php

declare(strict_types=1);

namespace CG\Core;

use ReflectionClass;
use ReflectionMethod;

abstract class ReflectionUtils
{

    public static function getOverrideableMethods(ReflectionClass $class, bool $publicOnly = false): array
    {
        $filter = ReflectionMethod::IS_PUBLIC;

        if (!$publicOnly) {
            $filter |= ReflectionMethod::IS_PROTECTED;
        }

        return array_filter(
            $class->getMethods($filter),
            static function(ReflectionMethod $method) { return !$method->isFinal() && !$method->isStatic(); }
        );
    }

    public static function getUnindentedDocComment(string $docComment): string
    {
        $docBlock = '';
        $lines = explode("\n", $docComment);
        $c = count($lines);
        foreach ($lines as $i => $iValue) {
            if (0 === $i) {
                $docBlock = $lines[0]."\n";
                continue;
            }

            $docBlock .= ' '.ltrim($iValue);

            if ($i+1 < $c) {
                $docBlock .= "\n";
            }
        }

        return $docBlock;
    }
}
