<?php

/**
 * Support Class for Validator Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Validator
 */

namespace CommonPHP\Core\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Thrown when an exception occurs in the Validator component
 */
class ValidatorException extends CoreException
{
    /** @var array Collection of validation errors */
    private array $validationErrors;

    /**
     * Instantiate this class
     *
     * @param array $validationErrors Collection of validation errors
     * @param int $code The error code
     * @param Throwable|null $previous Previous exception thrown used for exception chaining
     */
    #[Pure] public function __construct(array $validationErrors, int $code = 0, ?Throwable $previous = null)
    {
        $this->validationErrors = $validationErrors;
        $lines = [];
        foreach ($validationErrors as $error) {
            if (is_array($error)) {
                foreach ($error as $name => $value) {
                    if (is_string($name)) {
                        $lines[] = $name . ': ' . $value;
                    } else {
                        $lines[] = $value;
                    }
                }
            } else {
                $lines[] = $error;
            }
        }
        parent::__construct('- ' . implode("\n- ", $lines), $code, $previous);
    }

    /**
     * Add a validation error to the list
     *
     * @param string $error The validation error
     * @return $this
     */
    public function addValidationError(string $error): static
    {
        $this->validationErrors[] = $error;
        return $this;
    }

    /**
     * Check for validation errors
     *
     * @return bool
     */
    #[Pure] public function hasValidationErrors(): bool
    {
        return $this->countValidationErrors() > 0;
    }

    /**
     * Count the number of validation errors
     *
     * @return int
     */
    public function countValidationErrors(): int
    {
        return count($this->validationErrors);
    }

    /**
     * Get the validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}