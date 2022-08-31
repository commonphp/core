<?php

/**
 * DriverManager Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use ArrayIterator;
use Attribute;
use CommonPHP\Core\Attributes\Driver;
use CommonPHP\Core\Contracts\DriverContract;
use CommonPHP\Core\Enums\DriverMode;
use CommonPHP\Core\Exceptions\DriverException;
use CommonPHP\Core\Exceptions\InjectorException;
use IteratorAggregate;
use ReflectionClass;
use ReflectionException;
use Traversable;

/**
 * Allows dynamic drivers to be loaded and managed
 */
final class DriverManager implements IteratorAggregate
{
    /** @var Injector The Injector component */
    private Injector $injector;

    /** @var Inspector The Inspector component */
    private Inspector $inspector;

    /** @var DriverMode The mode that this manager operates under */
    private DriverMode $mode = DriverMode::Unmanaged;

    /** @var string The class name of the Driver attribute, must extend Driver */
    private string $attributeClass = Driver::class;

    /** @var string The class name of the Driver contract, must extend Driver Contract */
    private string $contractClass = DriverContract::class;

    /** @var Driver[] Collection of loaded drivers */
    private array $drivers = [];

    /** @var string When in Service mode, this is the only driver to be used */
    private string $serviceDriver;

    /**
     * Instantiate this class
     *
     * @param Injector $injector The Injector component
     * @throws DriverException
     */
    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
        try {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->inspector = $injector->instantiate(Inspector::class);
        } catch (InjectorException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the operation mode for the driver manager
     *
     * @return DriverMode
     */
    public function getMode(): DriverMode
    {
        return $this->mode;
    }

    /**
     * Set the operation mode for the driver manager
     *
     * @param DriverMode $mode The operation mode
     * @return $this
     * @throws DriverException
     */
    public function setMode(DriverMode $mode): self
    {
        if (count($this->drivers) > 0) throw new DriverException('Driver mode cannot be changed once drivers have been loaded');
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get the driver attribute class
     *
     * @return string
     */
    public function getAttributeClass(): string
    {
        return $this->attributeClass;
    }

    /**
     * Set the driver attribute class (must extend Driver)
     *
     * @param string $attributeClass The driver attribute class that extends Driver
     * @return $this
     * @throws DriverException
     */
    public function setAttributeClass(string $attributeClass): self
    {
        if (count($this->drivers) > 0) throw new DriverException('Attribute class cannot be changed once drivers have been loaded');
        try {
            $this->inspector->validateInheritanceOrInstance($attributeClass, Driver::class);
            $class = new ReflectionClass($attributeClass);
            $attribute = $this->inspector->getSingleReflectedAttribute($class, Attribute::class);
        } catch (Exceptions\InspectorException|ReflectionException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }
        if ($attribute->flags !== Attribute::TARGET_CLASS) {
            throw new DriverException($attributeClass . ' attribute ' . Attribute::class . ' must have only the TARGET_CLASS flag');
        }
        $this->attributeClass = $attributeClass;
        return $this;
    }

    /**
     * Get the driver contract class
     *
     * @return string
     */
    public function getContractClass(): string
    {
        return $this->contractClass;
    }

    /**
     * Set the driver contract class (must extend DriverContract)
     *
     * @param string $contractClass The driver contract class that extends Driver
     * @return self
     * @throws DriverException
     */
    public function setContractClass(string $contractClass): self
    {
        if (count($this->drivers) > 0) throw new DriverException('Contract class cannot be changed once drivers have been loaded');
        try {
            $this->inspector->validateInheritanceOrInstance($contractClass, DriverContract::class);
            $class = new ReflectionClass($contractClass);
        } catch (ReflectionException|Exceptions\InspectorException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }
        if (!($class->isAbstract() || $class->isInterface())) {
            throw new DriverException($contractClass . ' must be either an abstract class or interface');
        }
        $this->contractClass = $contractClass;
        return $this;
    }

    /**
     * Get a collection of loaded driver class names
     *
     * @return array
     */
    public function getLoadedClasses(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Check if a driver has been loaded
     *
     * @param string $driverClass The driver class to check for
     * @return bool
     */
    public function hasDriver(string $driverClass): bool
    {
        return array_key_exists($driverClass, $this->drivers);
    }

    /**
     * Load a driver (must extend driver contract and has driver attribute)
     *
     * @param string $driverClass The driver class to load
     * @return void
     * @throws DriverException
     */
    public function loadDriver(string $driverClass): void
    {
        if (array_key_exists($driverClass, $this->drivers)) return;
        try {
            $this->inspector->validateInheritance($driverClass, $this->contractClass);
            $class = new ReflectionClass($driverClass);
            $driver = $this->inspector->getSingleReflectedAttribute($class, $this->attributeClass, true);
        } catch (ReflectionException|Exceptions\InspectorException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }
        $this->drivers[$driverClass] = $driver;
    }

    /**
     * Get the instance of a loaded driver
     *
     * @param string $driverClass The class name of the driver
     * @param array $parameters The parameters to pass if the driver needs to be instantiated (otherwise ignored)
     * @param bool $triggerWarnings Trigger the warnings that occur, usually because of a driver class mismatch with Service mode
     * @return DriverContract
     * @throws DriverException
     */
    public function getDriver(string $driverClass, array $parameters = [], bool $triggerWarnings = true): DriverContract
    {
        $driver = $this->getDriverAttribute($driverClass);

        if ($driver === false) {
            throw new DriverException($driverClass . ' has not been loaded');
        }
        if ($this->mode === DriverMode::Service && isset($this->serviceDriver) && $driverClass !== $this->serviceDriver) {
            if ($triggerWarnings) trigger_error('Service driver ' . $driverClass . ' was called for but ' . $this->serviceDriver . ' is the existing service driver', E_USER_WARNING);
            $driverClass = $this->serviceDriver;
        }

        if ($driver->hasDriverInstance()) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $driver->getDriverInstance();
        }

        try {
            $instance = $this->injector->instantiate($driverClass, $parameters);
        } catch (InjectorException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }

        if ($this->mode === DriverMode::Service && !isset($this->serviceDriver)) {
            $this->serviceDriver = $driverClass;
        }

        if ($this->mode !== DriverMode::Unmanaged) {
            $driver->setDriverInstance($instance);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $instance;
    }

    /**
     * Get the driver attribute for the specified class
     *
     * @param string $driverClass The driver class
     * @return false|Driver
     */
    private function getDriverAttribute(string $driverClass): false|Driver
    {
        if (!array_key_exists($driverClass, $this->drivers)) {
            return false;
        }
        return $this->drivers[$driverClass];
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->drivers);
    }
}