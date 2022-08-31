<?php

/**
 * Support Class for Debugger Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Debugger
 */

namespace CommonPHP\Core\Definitions;

use Stringable;

/**
 * Defines a single step in a trace
 */
final class TraceStep implements Stringable
{
    /** @var string|null The function of the step */
    private ?string $function;

    /** @var int The line of the step */
    private int $line;

    /** @var string The file of the step */
    private string $file;

    /** @var string|null The class of the step */
    private ?string $class;

    /** @var object|null The reference object of the step */
    private ?object $object;

    /** @var string|null The type of call for the function (usually ::, -> or empty) */
    private ?string $type;

    /** @var array Arguments passed to the function or method */
    private array $arguments;

    /** @var string The concatenated function/method signature */
    private string $signature;

    /**
     * @param array $step The array of steps provided by debug_backtrace or Exception->getTrace
     */
    public function __construct(array $step)
    {
        // Extract all variables from the step
        extract($step, EXTR_PREFIX_ALL, 'step');

        $this->function = $step_function ?? null;
        $this->line = $step_line ?? 0;
        $this->file = $step_file ?? '{unknown}';
        $this->class = $step_class ?? null;
        $this->object = $step_object ?? null;
        $this->type = $step_type ?? null;
        $this->arguments = $step_args ?? [];

        $this->signature = ($this->class !== null ? $this->class . $this->type : '') . ($this->function ?? '');
    }

    /**
     * Get the function of the step
     *
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * Get the line of the step
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Get the file of the step
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get the class of the step
     *
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Get the reference object of the step
     *
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * Get the type of call for the function (usually ::, -> or empty)
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get the arguments passed to the function or method
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get the concatenated function/method signature
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $function = $this->signature;
        $file = $this->file;
        $line = $this->line;

        if ($function != '') $function = 'at ' . $function . ' ';
        $file = 'in file ' . $file;
        if ($line > 0) $line = ':' . $line;

        return '> ' . $function . $file . $line;
    }
}