<?php

declare(strict_types=1);

namespace CG\Proxy;

use ReflectionMethod;

/**
 * Interception Loader.
 *
 * Implementations of this interface are responsible for loading the interceptors
 * for a certain method.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface InterceptorLoaderInterface
{
    /**
     * Loads interceptors.
     *
     * @param ReflectionMethod $method
     * @return MethodInterceptorInterface[]
     * @noinspection PhpUnused
     */
    public function loadInterceptors(ReflectionMethod $method): array;
}
