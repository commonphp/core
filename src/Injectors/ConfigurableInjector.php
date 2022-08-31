<?php

/**
 * Support Class for Configuration Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Configuration
 */

namespace CommonPHP\Core\Injectors;

use CommonPHP\Core\Attributes\Configurable;
use CommonPHP\Core\Configuration;
use CommonPHP\Core\Contracts\InjectorContract;
use CommonPHP\Core\Exceptions\ConfigurationException;
use ReflectionClass;
use ReflectionException;

/**
 * Injects a class with the Configurable attribute
 */
final class ConfigurableInjector implements InjectorContract
{
    /** @var Configuration The Configuration component */
    private Configuration $configuration;

    /** @var array Collection of Configurable classes */
    private array $classes = [];

    /**
     * Instantiate this class
     *
     * @param Configuration $configuration The Configuration component
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     * @throws ConfigurationException
     */
    public function check(string $typeName, bool $isBuiltin): bool
    {
        if ($isBuiltin) return false;
        if (!array_key_exists($typeName, $this->classes)) {
            try {
                $class = new ReflectionClass($typeName);
            } catch (ReflectionException $e) {
                throw new ConfigurationException($e->getMessage(), 0, $e);
            }
            $this->classes[$typeName] = count($class->getAttributes(Configurable::class)) > 0;
        }
        return $this->classes[$typeName];
    }

    /**
     * @inheritDoc
     * @throws ConfigurationException
     */
    public function get(string $typeName, string $signature): object
    {
        if ($this->classes[$typeName] === false) {
            throw new ConfigurationException($signature . ' is not a valid Configurable object');
        }
        return $this->configuration->load($typeName);
    }
}