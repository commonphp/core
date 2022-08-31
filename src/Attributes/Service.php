<?php

/**
 * Support Class for ServiceManager Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\ServiceManager
 */

namespace CommonPHP\Core\Attributes;

use Attribute;

/**
 * Marks a class as s Service class
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Service
{
    /** @var string The fully-qualified name of the service class */
    private string $serviceClass;

    /** @var object The instance of the service */
    private object $serviceInstance;

    /**
     * Get the fully-qualified name of the service
     *
     * @return string
     */
    final public function getServiceClass(): string
    {
        return $this->serviceClass;
    }

    /**
     * Set the fully-qualified name of the service
     *
     * @param string $serviceClass The fully-qualified name of the service
     * @return void
     */
    final public function setServiceClass(string $serviceClass): void
    {
        $this->serviceClass = $serviceClass;
    }

    /**
     * Get the instance of the service
     *
     * @return object
     */
    final public function getServiceInstance(): object
    {
        return $this->serviceInstance;
    }

    /**
     * Set the instance of the service
     *
     * @param object $serviceInstance The instance of the service
     * @return void
     */
    final public function setServiceInstance(object $serviceInstance): void
    {
        $this->serviceInstance = $serviceInstance;
    }

    /**
     * Check if the instance of the service has been set
     *
     * @return bool
     */
    final public function hasServiceInstance(): bool
    {
        return isset($this->serviceInstance);
    }
}