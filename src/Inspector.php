<?php

/**
 * Inspector Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Definitions\InvocableMethod;
use CommonPHP\Core\Exceptions\CoreException;
use CommonPHP\Core\Exceptions\InspectorException;
use Generator;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Provides common inspection and reflection methods
 */
#[Service]
final class Inspector
{
    /**
     * Iterate through the properties of a class, skipping any static properties
     *
     * @param object|string $source The object or class name to iterate through
     * @param bool $skipStatic Should static properties be skipped?
     * @return Generator|ReflectionProperty[]
     * @throws ReflectionException
     */
    public function iterateProperties(object|string $source, bool $skipStatic = true): Generator|array
    {
        if (is_string($source)) {
            $source = new ReflectionClass($source);
        } else if (!($source instanceof ReflectionClass)) {
            $source = new ReflectionClass($source);
        }
        foreach ($source->getProperties() as $property) {
            if ($skipStatic && $property->isStatic()) continue;
            yield $property;
        }
        yield from [];
    }

    /**
     * Get a single attribute from a class, property, method or function
     *
     * @param ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $source The source class, property, method or function
     * @param string $attribute The attribute to get
     * @param bool $throwOnError Should an exception be thrown if the attribute is missing?
     * @return false|object
     * @throws InspectorException
     */
    public function getSingleReflectedAttribute(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $source, string $attribute, bool $throwOnError = false): false|object
    {
        $attrs = $source->getAttributes($attribute);
        if (count($attrs) === 0) {
            if ($throwOnError) {
                throw new InspectorException($source->getName() . ' is missing the ' . $attribute . ' attribute');
            }
            return false;
        }
        return $attrs[0]->newInstance();
    }

    /**
     * Get repeated attributes from a class, property, method or function
     *
     * @param ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $source The source class, property, method or function
     * @param string $attribute The attribute to get
     * @return array
     */
    public function getMultipleReflectedAttributes(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $source, string $attribute): array
    {
        $result = [];
        foreach ($source->getAttributes() as $attr) {
            if (is_a($attr->getName(), $attribute, true)) {
                $result[] = $attr->newInstance();
            }
        }
        return $result;
    }

    /**
     * Check if a single attribute exists on a class, property, method, or function
     *
     * @param ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $source The source class, property, method, or function
     * @param string $attribute The attribute to check for
     * @return bool
     */
    #[Pure] public function hasSingleReflectedAttribute(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $source, string $attribute): bool
    {
        return count($source->getAttributes($attribute)) > 0;
    }

    /**
     * Make sure that the subclass is a subclass of the superclass
     *
     * @param string|object $subclass The subclass to check
     * @param string|object $superclass The superclass to check
     * @return void
     * @throws InspectorException
     */
    public function validateInheritance(string|object $subclass, string|object $superclass): void
    {
        $subclass = $this->getClassName($subclass);
        $superclass = $this->getClassName($superclass);
        if (!is_subclass_of($subclass, $superclass)) {
            throw new InspectorException($subclass . ' must extend ' . $superclass);
        }
    }

    /**
     * Get the name of a class or object and make sure it exists
     *
     * @param string|object $class The class or object
     * @return string
     * @throws InspectorException
     */
    public function getClassName(string|object $class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        } else {
            if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
                throw new InspectorException($class . ' does not exist');
            }
        }
        return $class;
    }

    /**
     * Make sure that the supplied subclass is the same as the superclass
     *
     * @param string|object $subclass The subclass to check
     * @param string|object $superclass The superclass to check
     * @return void
     * @throws InspectorException
     */
    public function validateInstance(string|object $subclass, string|object $superclass): void
    {
        $subclass = $this->getClassName($subclass);
        $superclass = $this->getClassName($superclass);
        if ($subclass !== $superclass) {
            throw new InspectorException($subclass . ' must be an instance of ' . $superclass);
        }
    }

    /**
     * Make sure that the supplied subclass is a subclass of, or is the same as, the superclass
     *
     * @param string|object $subclass The subclass to check
     * @param string|object $superclass The superclass to check
     * @return void
     * @throws InspectorException
     */
    public function validateInheritanceOrInstance(string|object $subclass, string|object $superclass): void
    {
        $subclass = $this->getClassName($subclass);
        $superclass = $this->getClassName($superclass);
        if (!is_a($subclass, $superclass, true)) {
            throw new InspectorException($subclass . ' must extend or be an instance of ' . $superclass);
        }
    }

    /**
     * Create an InvocableMethod object from the supplied parameters
     *
     * @param ReflectionClass $class The class to check for
     * @param string $methodName The name of the method
     * @param bool $throwOnError Should an exception be thrown if the method is not found?
     * @param string $exceptionClass The exception class to be used by the InvocableMethod object
     * @return false|InvocableMethod
     * @throws InspectorException
     */
    public function getInvocableMethod(ReflectionClass $class, string $methodName, bool $throwOnError = true, string $exceptionClass = CoreException::class): false|InvocableMethod
    {
        if (!$class->hasMethod($methodName)) {
            if (!$throwOnError) return false;
            throw new InspectorException($methodName . ' is not a valid method on ' . $class->getName());
        }
        return new InvocableMethod($class, $methodName, $exceptionClass);
    }
}