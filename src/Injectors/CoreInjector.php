<?php

/**
 * Support Class for Injector Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Injector
 */

namespace CommonPHP\Core\Injectors;

use CommonPHP\Core\Arrayifier;
use CommonPHP\Core\Configuration;
use CommonPHP\Core\Contracts\InjectorContract;
use CommonPHP\Core\Debugger;
use CommonPHP\Core\Exceptions\CoreException;
use CommonPHP\Core\Exceptions\DriverException;
use CommonPHP\Core\Exceptions\InjectorException;
use CommonPHP\Core\Exceptions\ServiceException;
use CommonPHP\Core\Filesystem;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\ServiceManager;
use CommonPHP\Core\Validator;

/**
 * Provides Injector functionality for all CommonPHP Core Components
 */
final class CoreInjector implements InjectorContract
{
    /** @var Arrayifier The Arrayifier component */
    private Arrayifier $arrayifier;

    /** @var Configuration The Configuration component */
    private Configuration $configuration;

    /** @var Debugger The Debugger component */
    private Debugger $debugger;

    /** @var Filesystem The Filesystem component */
    private Filesystem $filesystem;

    /** @var Injector The Injector component */
    private Injector $injector;

    /** @var Inspector The Inspector component */
    private Inspector $inspector;

    /** @var ServiceManager The ServiceManager component */
    private ServiceManager $serviceManager;

    /** @var Validator The Validator component */
    private Validator $validator;

    /**
     * Instantiate this class
     *
     * @param Injector $injector The Injector component
     */
    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    /**
     * Get or create the Arrayifier component
     *
     * @return Arrayifier
     * @throws ServiceException
     * @throws InjectorException
     */
    public function getArrayifier(): Arrayifier
    {
        if (!isset($this->arrayifier))
        {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->arrayifier = $this->getServiceManager()->getService(Arrayifier::class);
        }
        return $this->arrayifier;
    }

    /**
     * Get or create the Configuration component
     *
     * @return Configuration
     * @throws ServiceException
     * @throws InjectorException
     */
    public function getConfiguration(): Configuration
    {
        if (!isset($this->configuration))
        {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->configuration = $this->getServiceManager()->getService(Configuration::class);
        }
        return $this->configuration;
    }

    /**
     * Get or create the Debugger component
     *
     * @return Debugger
     * @throws ServiceException
     * @throws InjectorException
     */
    public function getDebugger(): Debugger
    {
        if (!isset($this->debugger))
        {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->debugger = $this->getServiceManager()->getService(Debugger::class);
        }
        return $this->debugger;
    }

    /**
     * Get or create the Filesystem component
     *
     * @return Filesystem
     * @throws ServiceException
     * @throws InjectorException
     */
    public function getFilesystem(): Filesystem
    {
        if (!isset($this->filesystem))
        {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->filesystem = $this->getServiceManager()->getService(Filesystem::class);
        }
        return $this->filesystem;
    }

    /**
     * Get the Injector component
     *
     * @return Injector
     */
    public function getInjector(): Injector
    {
        return $this->injector;
    }

    /**
     * Get or create the Inspector component
     *
     * @return Inspector
     */
    public function getInspector(): Inspector
    {
        if (!isset($this->inspector))
        {
            $this->inspector = $this->getInjector()->getInspector();
        }
        return $this->inspector;
    }

    /**
     * Get or create the ServiceManager component
     *
     * @return ServiceManager
     * @throws InjectorException
     */
    public function getServiceManager(): ServiceManager
    {
        if (!isset($this->serviceManager))
        {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->serviceManager = $this->injector->instantiate(ServiceManager::class, [], true);
        }
        return $this->serviceManager;
    }

    /**
     * Get or create the Validator component
     *
     * @return Validator
     * @throws ServiceException
     * @throws InjectorException
     */
    public function getValidator(): Validator
    {
        if (!isset($this->validator))
        {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->validator = $this->getServiceManager()->getService(Validator::class);
        }
        return $this->validator;
    }

    /**
     * @inheritDoc
     */
    public function check(string $typeName, bool $isBuiltin): bool
    {
        return match($typeName) {
            Arrayifier::class,
            Configuration::class,
            Debugger::class,
            Filesystem::class,
            Injector::class,
            Inspector::class,
            ServiceManager::class,
            Validator::class => true,
            default => false
        };
    }

    /**
     * @inheritDoc
     * @throws CoreException
     * @throws ServiceException
     * @throws DriverException
     * @throws InjectorException
     */
    public function get(string $typeName, string $signature): object
    {
        return match($typeName) {
            Arrayifier::class => $this->getArrayifier(),
            Configuration::class => $this->getConfiguration(),
            Debugger::class => $this->getDebugger(),
            Filesystem::class => $this->getFilesystem(),
            Injector::class => $this->getInjector(),
            Inspector::class => $this->getInspector(),
            ServiceManager::class => $this->getServiceManager(),
            Validator::class => $this->getValidator(),
            default => throw new CoreException('Unsupported core injection type: '.$typeName.' from '.$signature)
        };
    }
}