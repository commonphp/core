<?php

/**
 * Support Class for CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 */

namespace CommonPHP\Core\Definitions;

use CommonPHP\Core\Exceptions\CoreException;
use CommonPHP\Core\Injector;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use Throwable;

/**
 * Defines an invocable method
 */
final class InvocableMethod
{
    /** @var ReflectionClass The reflected class where the method should exist */
    private ReflectionClass $class;

    /** @var string The name of the method */
    private string $method;

    /** @var string The exception class to use on error */
    private string $exceptionClass;

    /**
     * Instantiate this class
     *
     * @param ReflectionClass $class The reflected class where the method should exist
     * @param string $method The name of hte method
     * @param string $exceptionClass The exception class to use on error
     */
    public function __construct(ReflectionClass $class, string $method, string $exceptionClass = CoreException::class)
    {
        if (!$class->hasMethod($method)) {
            throw new ($exceptionClass)('Method ' . $method . ' does not exist on class ' . $class->getName());
        }
        $this->class = $class;
        $this->method = $method;
        $this->exceptionClass = $exceptionClass;
    }

    /**
     * Get the class name
     *
     * @return string
     */
    #[Pure] public function getClass(): string
    {
        return $this->class->getName();
    }

    /**
     * Invoke the method
     *
     * @param Injector $injector The Injector component
     * @param object $target The source target to invoke against
     * @param array $parameters Parameters to pass to invocation
     * @return mixed
     */
    public function invoke(Injector $injector, object $target, array $parameters = []): mixed
    {
        try {
            return $injector->invoke($target, $this->getMethod(), $parameters);
        } catch (Throwable $e) {
            throw new ($this->exceptionClass)($e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the name of the method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}