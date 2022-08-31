<?php

/**
 * Configuration Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Configurable;
use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Collections\ConfigurationDriverCollection;
use CommonPHP\Core\Drivers\JsonConfigurationDriver;
use CommonPHP\Core\Exceptions\ConfigurationException;
use CommonPHP\Core\Exceptions\InjectorException;
use CommonPHP\Core\Exceptions\InspectorException;
use CommonPHP\Core\Injectors\ConfigurableInjector;
use ReflectionClass;
use ReflectionException;

/**
 * Provide the ability to read, write and check for configuration files
 */
#[Service]
final class Configuration
{
    /** @var Arrayifier The Arrayifier component */
    private Arrayifier $arrayifier;

    /** @var ConfigurationDriverCollection The collection of configuration drivers */
    private ConfigurationDriverCollection $driverCollection;

    /** @var Inspector The Inspector component */
    private Inspector $inspector;

    /** @var array[] Collection of loaded configurations */
    private array $loaded = [];
    
    /** @var Configurable[] Collection of configurable classes */
    private array $configurable = [];

    /**
     * Instantiate this class
     * 
     * @param Arrayifier $arrayifier The Arrayifier component
     * @param Injector $injector The Injector component
     * @param Inspector $inspector The Inspector component
     * @param ConfigurationDriverCollection $drivers The collection of configuration drivers
     * @throws Exceptions\DriverException
     * @throws InjectorException
     */
    public function __construct(Arrayifier $arrayifier, Injector $injector, Inspector $inspector, ConfigurationDriverCollection $drivers)
    {
        $injector->loadInjector(new ConfigurableInjector($this));
        $this->arrayifier = $arrayifier;
        $drivers->load(JsonConfigurationDriver::class);
        $this->driverCollection = $drivers;
        $this->inspector = $inspector;
    }

    /**
     * Load a configuration into an object or class
     * 
     * @param string|object $target The object or class name to load
     * @return object
     * @throws ConfigurationException
     */
    public function load(string|object $target): object
    {
        $configurable = $this->getConfigurableAttribute($target, true);
        $data = $this->read($configurable->getName());
        try {
            return $this->arrayifier->populate($target, $data);
        } catch (Exceptions\ArrayifierException $e) {
            throw new ConfigurationException('An exception occurred within the Arrayifier component', 0, $e);
        }
    }

    /**
     * Get the configurable attribute from an object or class name
     * 
     * @param string|object $object The object or class name to reflection
     * @param bool $throwOnError Should an error be thrown on error?
     * @return false|Configurable
     * @throws ConfigurationException
     */
    private function getConfigurableAttribute(string|object $object, bool $throwOnError = false): false|Configurable
    {
        if (is_object($object)) $object = get_class($object);
        if (!$this->isConfigurable($object)) {
            if (!$throwOnError) return false;
            throw new ConfigurationException($object . ' does not have the ' . Configurable::class . ' attribute');
        }
        return $this->configurable[$object];
    }

    /**
     * Check if a class or object has the Configurable attribute, and store the result
     * 
     * @param string|object $object The object or class to reflect
     * @return bool
     * @throws ConfigurationException
     */
    public function isConfigurable(string|object $object): bool
    {
        if (is_object($object)) $object = get_class($object);
        if (!array_key_exists($object, $this->loaded)) {
            try {
                $class = new ReflectionClass($object);
                $this->configurable[$class->getName()] = $this->inspector->getSingleReflectedAttribute($class, Configurable::class);
            } catch (ReflectionException|InspectorException $e) {
                throw new ConfigurationException($e->getMessage(), 0, $e);
            }
        }
        return $this->configurable[$object] !== false;
    }

    /**
     * Read configuration data
     * 
     * @param string $name The name of the configuration to read
     * @return array
     * @throws ConfigurationException
     */
    public function read(string $name): array
    {
        if (array_key_exists($name, $this->loaded)) return $this->loaded[$name];
        $driver = $this->driverCollection->find($name);
        if (!$driver->exists($name)) {
            throw new ConfigurationException('Configuration \'' . $name . '\' does not exist');
        }
        $result = $driver->read($name);
        $this->loaded[$name] = $result;
        return $result;
    }

    /**
     * Write configuration data from an object
     *
     * @param object $source The Configurable object to save
     * @return void
     * @throws ConfigurationException
     */
    public function save(object $source): void
    {
        try {
            $data = $this->arrayifier->arrayify($source);
        } catch (Exceptions\ArrayifierException $e) {
            throw new ConfigurationException('An exception occurred within the Arrayifier component', 0, $e);
        }
        $attr = $this->getConfigurableAttribute($source, true);
        $this->write($attr->getName(), $data);
    }

    /**
     * Write configuration data
     *
     * @param string $name The name of the configuration
     * @param array $data The data to write
     * @return void
     * @throws ConfigurationException
     */
    public function write(string $name, array $data): void
    {
        $this->driverCollection->find($name)->write($name, $data);
        $this->loaded[$name] = $data;
    }

    /**
     * Check if a configuration exists
     *
     * @param string $name The name of the configuration
     * @return bool
     * @throws ConfigurationException
     */
    public function exists(string $name): bool
    {
        return $this->driverCollection->find($name)->exists($name);
    }
}