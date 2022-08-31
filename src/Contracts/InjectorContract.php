<?php

/**
 * Support Class for Injector Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Injector
 */

namespace CommonPHP\Core\Contracts;

/**
 * Functionality required by all Injectors
 */
interface InjectorContract
{
    /**
     * Check if a type is included in this injector
     *
     * @param string $typeName The name of the type to check
     * @param bool $isBuiltin Check if the value is a builtin or system class
     * @return bool
     */
    public function check(string $typeName, bool $isBuiltin): bool;

    /**
     * Get a type based on the name
     *
     * @param string $typeName The name of the type to get
     * @param string $signature The signature of the source for use in messages and exceptions
     * @return object
     */
    public function get(string $typeName, string $signature): object;
}