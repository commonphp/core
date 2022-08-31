<?php

/**
 * Support Class for Configuration Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Configuration
 */

namespace CommonPHP\Core\Contracts;

/**
 * Functionality required by all configuration drivers
 */
interface ConfigurationDriverContract extends DriverContract
{
    /**
     * Read configuration data
     *
     * @param string $name The name of the configuration
     * @return array
     */
    public function read(string $name): array;

    /**
     * Write configuration data
     *
     * @param string $name The name of the configuration
     * @param array $data The data to write
     * @return void
     */
    public function write(string $name, array $data): void;

    /**
     * Check if a configuration exists
     *
     * @param string $name The name of the configuration
     * @return bool
     */
    public function exists(string $name): bool;
}