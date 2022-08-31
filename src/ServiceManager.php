<?php

/**
 * ServiceManager Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Exceptions\ServiceException;
use ReflectionClass;
use ReflectionException;

/**
 * Allows a class to be flagged as a single-instance class, allowing the same object to be reused in many classes
 */
#[Service]
final class ServiceManager
{
    /** @var Injector The Injector component */
    private Injector $injector;

    /** @var Inspector The Inspector component */
    private Inspector $inspector;

    /** @var Service[]|false[] Collection of services */
    private array $services = [];

    /**
     * Instantiate this class
     *
     * @param Injector $injector The Injector component
     * @throws ServiceException
     */
    final public function __construct(Injector $injector)
    {
        $this->injector = $injector;
        $this->inspector = $injector->getInspector();
        $this->addService(Injector::class, $injector);
        $this->addService(Inspector::class, $injector->getInspector());
        $this->addService(ServiceManager::class, $this);
    }

    /**
     * Add a service class to the service manager
     *
     * @param string $serviceClass The class of the service
     * @param object $instance The instance of the class, must inherit or be an instance of the supplied class name
     * @return void
     * @throws ServiceException
     */
    public function addService(string $serviceClass, object $instance): void
    {
        try {
            $this->inspector->validateInheritanceOrInstance($instance, $serviceClass);
        } catch (Exceptions\InspectorException $e) {
            throw new ServiceException($e->getMessage(), 0, $e);
        }
        if (!$this->isService($serviceClass)) {
            throw new ServiceException($serviceClass . ' is not a valid Service');
        }
        $service = $this->getServiceAttribute($serviceClass);
        if ($service->hasServiceInstance()) {
            throw new ServiceException($serviceClass . ' already exists');
        } else if ($service->getServiceClass() !== $serviceClass) {
            throw new ServiceException($serviceClass . ' is already a registered Service as ' . $service->getServiceClass());
        }
        $service->setServiceInstance($instance);
    }

    /**
     * Check if a class is a service
     *
     * @param string $serviceClass The name of the class to check
     * @return bool
     * @throws ServiceException
     */
    public function isService(string $serviceClass): bool
    {
        if (!array_key_exists($serviceClass, $this->services)) {
            try {
                $class = new ReflectionClass($serviceClass);
                /** @var false|Service $service */
                $service = $this->inspector->getSingleReflectedAttribute($class, Service::class);
            } catch (ReflectionException $e) {
                throw new ServiceException($e->getMessage(), 0, $e);
            } catch (Exceptions\InspectorException) {
                $service = false;
            }
            if ($service !== false && isset($class)) {
                $service->setServiceClass($class->getName());
            }
            $this->services[$serviceClass] = $service;
        }
        return $this->services[$serviceClass] !== false;
    }

    /**
     * Get the service attribute of a class
     *
     * @param string $serviceClass The class to get the attribute from
     * @return bool|Service
     */
    private function getServiceAttribute(string $serviceClass): bool|Service
    {
        if (!array_key_exists($serviceClass, $this->services)) return false;
        return $this->services[$serviceClass];
    }

    /**
     * Check if the service manager has a service
     *
     * @param string $serviceClass The service to check for
     * @return bool
     */
    public function hasService(string $serviceClass): bool
    {
        return array_key_exists($serviceClass, $this->services) && $this->services[$serviceClass] !== false;
    }

    /**
     * Get a service
     *
     * @param string $serviceClass The class of the service to get
     * @return object
     * @throws ServiceException
     */
    public function getService(string $serviceClass): object
    {
        $service = $this->isService($serviceClass) ? $this->getServiceAttribute($serviceClass) : false;
        if ($service === false) {
            throw new ServiceException($serviceClass . ' is not a valid Service class');
        }
        if (!$service->hasServiceInstance()) {
            try {
                $service->setServiceInstance($this->injector->instantiate($serviceClass, [], true));
            } catch (Exceptions\InjectorException $e) {
                throw new ServiceException($e->getMessage(), 0, $e);
            }
        }
        return $service->getServiceInstance();
    }
}