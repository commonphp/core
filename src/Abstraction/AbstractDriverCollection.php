<?php

/**
 * Support Class for DriverManager Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\DriverManager
 */

namespace CommonPHP\Core\Abstraction;

use CommonPHP\Core\Contracts\DriverContract;
use CommonPHP\Core\DriverManager;
use CommonPHP\Core\Enums\DriverMode;
use CommonPHP\Core\Exceptions\DriverException;
use CommonPHP\Core\Injector;
use JetBrains\PhpStorm\Pure;

/**
 * Base functionality required for driver collections
 */
abstract class AbstractDriverCollection
{
    /** @var DriverManager The DriverManager component */
    private DriverManager $driverManager;

    /**
     * Instantiate this class
     *
     * @param Injector $injector The Injector component
     * @param DriverMode $mode The mode the driver is operating as
     * @param string $attributeClass The class name for the driver attribute
     * @param string $contractClass The interface/class name for the driver contract
     * @throws DriverException
     */
    public function __construct(Injector $injector, DriverMode $mode, string $attributeClass, string $contractClass)
    {
        $this->driverManager = new DriverManager($injector);
        $this->driverManager->setMode($mode);
        $this->driverManager->setAttributeClass($attributeClass);
        $this->driverManager->setContractClass($contractClass);
    }

    /**
     * Get the driver manager
     *
     * @return DriverManager
     */
    protected function getDriverManager(): DriverManager
    {
        return $this->driverManager;
    }

    /**
     * Check if a driver class has been loaded
     *
     * @param string $driverClass The class of the driver
     * @return bool
     */
    #[Pure] protected function hasDriverClass(string $driverClass): bool
    {
        return $this->driverManager->hasDriver($driverClass);
    }

    /**
     * Load a driver class
     *
     * @param string $driverClass The class to load
     * @return void
     * @throws DriverException
     */
    protected function loadDriverClass(string $driverClass): void
    {
        $this->driverManager->loadDriver($driverClass);
    }

    /**
     * Get a driver by class name
     *
     * @param string $driverClass The class name of the driver
     * @param array $parameters Any parameters to pass on instantiation
     * @return DriverContract
     * @throws DriverException
     */
    protected function getDriver(string $driverClass, array $parameters = []): DriverContract
    {
        return $this->driverManager->getDriver($driverClass, $parameters);
    }
}