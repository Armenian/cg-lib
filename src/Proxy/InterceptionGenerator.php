<?php

declare(strict_types=1);

namespace CG\Proxy;

use CG\Core\ClassUtils;
use CG\Core\ReflectionUtils;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use CG\Generator\PhpMethod;
use CG\Generator\PhpClass;
use Closure;
use ReflectionClass;
use ReflectionException;

/**
 * Interception Generator.
 *
 * This generator creates joinpoints to allow for AOP advices. Right now, it only
 * supports the most powerful around advice.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class InterceptionGenerator implements GeneratorInterface
{
    private string $prefix = '__CGInterception__';
    private ?Closure $filter = null;
    private string $requiredFile;

    public function setRequiredFile(string $file): void
    {
        $this->requiredFile = $file;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setFilter(Closure $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param ReflectionClass $originalClass
     * @param PhpClass $genClass
     * @throws ReflectionException
     */
    public function generate(ReflectionClass $originalClass, PhpClass $genClass): void
    {
        $methods = ReflectionUtils::getOverrideableMethods($originalClass);

        if (null !== $this->filter) {
            $methods = array_filter($methods, $this->filter);
        }

        if (empty($methods)) {
            return;
        }

        if (!empty($this->requiredFile)) {
            $genClass->addRequiredFile($this->requiredFile);
        }

        $interceptorLoader = new PhpProperty();
        $interceptorLoader
            ->setName($this->prefix.'loader')
            ->setVisibility(PhpProperty::VISIBILITY_PRIVATE)
        ;
        $genClass->setProperty($interceptorLoader);

        $loaderSetter = new PhpMethod();
        $loaderSetter
            ->setName($this->prefix.'setLoader')
            ->setVisibility(PhpMethod::VISIBILITY_PUBLIC)
            ->setBody('$this->'.$this->prefix.'loader = $loader;')
        ;
        $genClass->setMethod($loaderSetter);
        $loaderParam = new PhpParameter();
        $loaderParam
            ->setName('loader')
            ->setType(InterceptorLoaderInterface::class)
        ;
        $loaderSetter->addParameter($loaderParam);

        $interceptorCode =
            '$ref = new \ReflectionMethod(%s, %s);'."\n"
            .'$interceptors = $this->'.$this->prefix.'loader->loadInterceptors($ref, $this, [%s]);'."\n"
            .'$invocation = new \CG\Proxy\MethodInvocation($ref, $this, [%s], $interceptors);'."\n\n"
        ;

        $voidReturns = [
            true => '$invocation->proceed();',
            false => 'return $invocation->proceed();'
        ];

        foreach ($methods as $method) {
            $params = [];
            foreach ($method->getParameters() as $param) {
                $params[] = '$'.$param->name;
            }
            $params = implode(', ', $params);

            $genMethod = PhpMethod::fromReflection($method);

            $isVoid = 'void' === $genMethod->getReturnType();

            $genMethod->setBody(sprintf($interceptorCode . $voidReturns[$isVoid], var_export(ClassUtils::getUserClass($method->class), true), var_export($method->name, true), $params, $params))
                ->setDocblock(null)
            ;
            $genClass->setMethod($genMethod);
        }
    }
}
