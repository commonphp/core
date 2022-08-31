<?php

/**
 * Support Class for Validator Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Validator
 */

namespace CommonPHP\Core\Contracts;

/**
 * Functionality required by all validators
 */
interface ValidatorContract
{
    /**
     * Check if a value passes validation
     *
     * @param string $name The name of the value
     * @param mixed $value The value to check
     * @param array $errors Returned array of validation errors
     * @return bool
     */
    public function check(string $name, mixed $value, array &$errors): bool;
}