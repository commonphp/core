<?php

/**
 * Support Class for Validator Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Validator
 */

namespace CommonPHP\Core\Validators;

use Attribute;
use CommonPHP\Core\Contracts\ValidatorContract;

/**
 * Validate an email address
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class EmailValidator implements ValidatorContract
{
    /** @var string The message to return on error */
    private string $message;

    /**
     * Instantiate this class
     *
     * @param string $message The message to return on error
     */
    public function __construct(string $message = '{name} must be an email address')
    {
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function check(string $name, mixed $value, array &$errors): bool
    {
        $result = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        if (!$result) {
            $errors[] = $this->getMessage($name);
        }
        return $result;
    }

    /**
     * Get the message to return on error
     *
     * @param string $name The name of the value being validated
     * @return string
     */
    public function getMessage(string $name): string
    {
        return str_replace('{name}', $name, $this->message);
    }
}