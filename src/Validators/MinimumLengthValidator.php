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
 * Require the value to have a minimum length
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class MinimumLengthValidator implements ValidatorContract
{
    /** @var int The minimum value length */
    private int $length;

    /** @var string The message to return on error */
    private string $message;

    /**
     * Instantiate this class
     *
     * @param int $length The minimum value length
     * @param string $message The message to return on error
     */
    public function __construct(int $length, string $message = '{name} must be at-least {length} characters long')
    {
        if ($length < 1) $length = 1;
        $this->length = $length;
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function check(string $name, mixed $value, array &$errors): bool
    {
        if (is_string($value) && strlen($value) < $this->getLength()) {
            $errors[] = $this->getMessage($name);
            return false;
        }
        return true;
    }

    /**
     * Get the minimum value length
     *
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Get the message to return on error
     *
     * @param string $name The name of the value being validated
     * @return string
     */
    public function getMessage(string $name): string
    {
        return str_replace(['{name}', '{length}'], [$name, $this->length], $this->message);
    }
}