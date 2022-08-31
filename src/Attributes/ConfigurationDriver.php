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
 * Mark a class as a configuration driver
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ConfigurationDriver extends Driver
{
    /** @var string|null The pattern to compare the configuration name against */
    private ?string $pattern;

    /**
     * Instantiate this class
     *
     * @param string $pattern The pattern to compare the configuration name against
     */
    public function __construct(string $pattern)
    {
        if (preg_match($pattern, '') === false) {
            trigger_error('Invalid regex pattern: ' . $pattern, E_USER_WARNING);
            $pattern = null;
        }
        $this->pattern = $pattern;
    }

    /**
     * Get the pattern to compare the configuration name against
     *
     * @return string|null.
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }
}