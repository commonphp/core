<?php

/**
 * Support Class for Configuration Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Configuration
 */

namespace CommonPHP\Core\Attributes;

use Attribute;

/**
 * Marks a class as configurable
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Configurable
{
    /** @var string The configuration name */
    private string $name;

    /**
     * Instantiate this class
     *
     * @param string $name The configuration name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the configuration name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}