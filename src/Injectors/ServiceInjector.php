<?php

/**
 * Support Class for ServiceManager Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\ServiceManager
 */

namespace CommonPHP\Core\Injectors;

use CommonPHP\Core\Contracts\InjectorContract;
use CommonPHP\Core\Exceptions\ServiceException;
use CommonPHP\Core\ServiceManager;

/**
 * Inject a service class provided by the ServiceManager
 */
final class ServiceInjector implements InjectorContract
{
    /** @var ServiceManager The ServiceManager component */
    private ServiceManager $serviceManager;

    /**
     * Instantiate this class
     *
     * @param ServiceManager $serviceManager The ServiceManager component
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @inheritDoc
     * @throws ServiceException
     */
    public function check(string $typeName, bool $isBuiltin): bool
    {
        return !$isBuiltin && $this->serviceManager->isService($typeName);
    }

    /**
     * @inheritDoc
     * @throws ServiceException
     */
    public function get(string $typeName, string $signature): object
    {
        return $this->serviceManager->getService($typeName);
    }
}