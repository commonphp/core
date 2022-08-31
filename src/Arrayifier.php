<?php

/**
 * Arrayifier Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Arrayable;
use CommonPHP\Core\Attributes\Populatable;
use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Exceptions\ArrayifierException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Converts an object to and from an array
 */
#[Service]
final class Arrayifier
{
    /** @var Injector The Injector component */
    private Injector $injector;

    /** @var Inspector The Inspector component */
    private Inspector $inspector;

    /**
     * Instantiate this class
     *
     * @param Injector $injector The Injector component
     * @param Inspector $inspector The Inspector component
     */
    public function __construct(Injector $injector, Inspector $inspector)
    {
        $this->injector = $injector;
        $this->inspector = $inspector;
    }

    /**
     * Convert an object to an array
     *
     * @param object $source The object to convert
     * @return array
     * @throws ArrayifierException
     */
    public function arrayify(object $source): array
    {
        $data = [];
        $class = new ReflectionClass($source);
        try {
            $properties = $this->inspector->iterateProperties($class);
        } catch (ReflectionException $e) {
            throw new ArrayifierException($e->getMessage(), 0, $e);
        }
        foreach ($properties as $property) {
            $arrayable = $this->getArrayableAttribute($property);
            if ($arrayable === false) continue;
            if ($arrayable->hasMethod()) {
                try {
                    $data[$property->getName()] = $this->inspector->getInvocableMethod($class, $arrayable->getMethod(), ArrayifierException::class)
                        ->invoke($this->injector, $source);
                } catch (Exceptions\InspectorException $e) {
                    throw new ArrayifierException($e->getMessage(), 0, $e);
                }
            } else {
                $data[$property->getName()] = $property->isInitialized($source) ? $property->getValue($source) : null;
            }
        }
        return $data;
    }

    /**
     * Get the Arrayable attribute from the supplied property
     *
     * @param ReflectionProperty $property The property to get the attribute from
     * @return false|Arrayable
     * @throws ArrayifierException
     */
    private function getArrayableAttribute(ReflectionProperty $property): false|Arrayable
    {
        try {
            return $this->inspector->getSingleReflectedAttribute($property, Arrayable::class);
        } catch (Exceptions\InspectorException $e) {
            throw new ArrayifierException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Populate an object or class with an array
     *
     * @param string|object $target The class name or object to populate
     * @param array $data The array to populate from
     * @return object
     * @throws ArrayifierException
     */
    public function populate(string|object $target, array $data): object
    {
        try {
            if (!is_object($target)) {
                $target = $this->injector->instantiate($target, [], true);
            }
            $class = new ReflectionClass($target);
            $properties = $this->inspector->iterateProperties($class);
        } catch (Exceptions\InjectorException|ReflectionException $e) {
            throw new ArrayifierException($e->getMessage(), 0, $e);
        }
        foreach ($properties as $property) {
            if (!array_key_exists($property->getName(), $data)) continue; // We don't want to worry about properties that weren't passed
            $populatable = $this->getPopulatableAttribute($property);
            if ($populatable === false) continue;
            if ($populatable->hasMethod()) {
                try {
                    $this->inspector->getInvocableMethod($class, $populatable->getMethod(), ArrayifierException::class)
                        ->invoke($this->injector, $target, ['value' => $data[$property->getName()]]);
                } catch (Exceptions\InspectorException $e) {
                    throw new ArrayifierException($e->getMessage(), 0, $e);
                }
            } else {
                $property->setValue($target, $data[$property->getName()]);
            }
        }
        return $target;
    }

    /**
     * Get the Populatable attribute from a property
     *
     * @param ReflectionProperty $property The property to get the Populatable attribute from
     * @return false|Populatable
     * @throws ArrayifierException
     */
    private function getPopulatableAttribute(ReflectionProperty $property): false|Populatable
    {
        try {
            return $this->inspector->getSingleReflectedAttribute($property, Populatable::class);
        } catch (Exceptions\InspectorException $e) {
            throw new ArrayifierException($e->getMessage(), 0, $e);
        }
    }
}