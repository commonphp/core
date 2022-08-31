<?php

/**
 * Support Class for Configuration Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Configuration
 */

namespace CommonPHP\Core\Collections;

use CommonPHP\Core\Abstraction\AbstractDriverCollection;
use CommonPHP\Core\Attributes\ConfigurationDriver;
use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Contracts\ConfigurationDriverContract;
use CommonPHP\Core\Debugger;
use CommonPHP\Core\Enums\DriverMode;
use CommonPHP\Core\Exceptions\ConfigurationException;
use CommonPHP\Core\Exceptions\DriverException;
use CommonPHP\Core\Injector;
use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Driver collection for the Configuration component
 */
#[Service]
final class ConfigurationDriverCollection extends AbstractDriverCollection
{
    /** @var Debugger The Debugger component */
    private Debugger $debugger;

    /**
     * Instantiate this class
     *
     * @param Injector $injector The Injector component
     * @param Debugger $debugger The Debugger component
     * @throws ConfigurationException
     */
    public function __construct(Injector $injector, Debugger $debugger)
    {
        $this->debugger = $debugger;
        try {
            parent::__construct($injector, DriverMode::Managed, ConfigurationDriver::class, ConfigurationDriverContract::class);
        } catch (DriverException $e) {
            throw new ConfigurationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if a driver has been loaded
     *
     * @param string $driverClass The class name of the driver
     * @return bool
     */
    #[Pure] public function has(string $driverClass): bool
    {
        return $this->hasDriverClass($driverClass);
    }

    /**
     * Load a configuration driver
     *
     * @param string $driverClass The class name of the configuration driver
     * @return void
     * @throws DriverException
     */
    public function load(string $driverClass): void
    {
        if ($this->hasDriverClass($driverClass)) return;
        $this->loadDriverClass($driverClass);
    }

    /**
     * Find a configuration driver based on the configuration name
     *
     * @param string $name The configuration name
     * @param bool $throwOnError [optional] Throw an exception when the configuration driver can't be found or return false
     * @return false|ConfigurationDriverContract
     * @throws ConfigurationException
     */
    public function find(string $name, bool $throwOnError = true): false|ConfigurationDriverContract
    {
        /** @var ConfigurationDriver[] $drivers */
        try {
            $drivers = $this->getDriverManager()->getIterator();
        } catch (Exception $e) {
            throw new ConfigurationException($e->getMessage(), 0, $e);
        }
        foreach ($drivers as $driverName => $driver) {
            $matched = preg_match($driver->getPattern(), $name);
            if ($matched !== false && $matched > 0) {
                try {
                    /** @noinspection PhpIncompatibleReturnTypeInspection */
                    return $this->getDriver($driverName);
                } catch (DriverException $e) {
                    if (!$throwOnError) {
                        $this->debugger->logException($e);
                        return false;
                    }
                    throw new ConfigurationException('An exception occurred in the Driver Manager', 0, $e);
                }
            }
        }
        if (!$throwOnError) return false;
        throw new ConfigurationException('There were no configuration drivers loaded that seem to handle \'' . $name . '\'');
    }
}