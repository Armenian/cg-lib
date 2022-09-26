<?php

declare(strict_types=1);

namespace CG\Proxy;

use ReflectionMethod;

class RegexInterceptionLoader implements InterceptorLoaderInterface
{
    private array $interceptors;

    public function __construct(array $interceptors = [])
    {
        $this->interceptors = $interceptors;
    }

    /** @noinspection PhpUnused */
    public function loadInterceptors(ReflectionMethod $method): array
    {
        $signature = $method->class.'::'.$method->name;

        $matchingInterceptors = [];
        foreach ($this->interceptors as $pattern => $interceptor) {
            if (preg_match('#'.$pattern.'#', $signature)) {
                $matchingInterceptors[] = $this->initializeInterceptor($interceptor);
            }
        }

        return $matchingInterceptors;
    }

    protected function initializeInterceptor($interceptor)
    {
        return $interceptor;
    }
}
