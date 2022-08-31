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
 * Validate a value against a regular expression pattern
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class RegexValidator implements ValidatorContract
{
    /** @var string The regular expression pattern */
    private string $pattern;

    /** @var string The message to return on error */
    private string $message;

    /**
     * Instantiate this class
     *
     * @param string $pattern The regular expression pattern
     * @param string $message The message to return on error
     */
    public function __construct(string $pattern, string $message = '{name} does not match the required pattern: {pattern}')
    {
        $this->pattern = $pattern;
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function check(string $name, mixed $value, array &$errors): bool
    {
        if (!$this->matches($value ?? '')) {
            $errors[] = $this->getMessage($name);
            return false;
        }
        return true;
    }

    /**
     * Check if a value matches the regular expression pattern
     *
     * @param string|int|float|bool $input The value to test
     * @return bool
     */
    public function matches(string|int|float|bool $input): bool
    {
        if (is_bool($input)) $input = $input ? 1 : 0;
        $result = preg_match($this->pattern, $input);
        return $result !== false && $result > 0;
    }

    /**
     * Get the message to return on error
     *
     * @param string $name The name of the value being validated
     * @return string
     */
    public function getMessage(string $name): string
    {
        return str_replace(['{name}', '{pattern}'], [$name, $this->pattern], $this->message);
    }
}