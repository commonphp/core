<?php

/**
 * Support Class for Arrayifier Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Arrayifier
 */

namespace CommonPHP\Core\Attributes;

use Attribute;

/**
 * Flags a class property as arrayable for the Arrayifier component
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Arrayable
{
    /** @var string|null The method to call on arrayify */
    private ?string $method;

    /**
     * Instantiate this class
     *
     * @param string|null $method The method to call on arrayify
     */
    public function __construct(?string $method = null)
    {
        $this->method = $method;
    }

    /**
     * Check if there is a method to call on arrayify
     *
     * @return bool
     */
    public function hasMethod(): bool
    {
        return $this->method !== null;
    }

    /**
     * Get the method to call on arrayify
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}