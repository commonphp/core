<?php

/**
 * Support Class for DriverManager Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\DriverManager
 */

namespace CommonPHP\Core\Attributes;

use Attribute;

/**
 * Mark a class as a driver or extend a different attribute if additional functionality is needed
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Driver
{
    /** @var object The instance of the driver */
    private object $driverInstance;

    /**
     * Check if the instance of the driver
     *
     * @return bool
     */
    final public function hasDriverInstance(): bool
    {
        return isset($this->driverInstance);
    }

    /**
     * Get the instance of the driver
     *
     * @return object
     */
    final public function getDriverInstance(): object
    {
        return $this->driverInstance;
    }

    /**
     * Set the instance of the driver
     *
     * @param object $driverInstance The instance of the driver
     * @return void
     */
    final public function setDriverInstance(object $driverInstance): void
    {
        $this->driverInstance = $driverInstance;
    }
}