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

use CommonPHP\Core\Enums\DebuggerSeverity;
use Stringable;

/**
 * Defines a trace for the debugger
 */
final class Trace implements Stringable
{
    /** @var DebuggerSeverity The severity of the trace */
    private DebuggerSeverity $severity;

    /** @var string The title of the trace */
    private string $title;

    /** @var string The message of the trace */
    private string $message;

    /** @var string The name of the file the trace occurred in */
    private string $file;

    /** @var int The line in the file the trace occurred on */
    private int $line;

    /** @var TraceStep[] Collection of steps in this trace */
    private array $steps;

    /** @var Trace|null The previous trace */
    private ?Trace $previous;

    /**
     * Instantiate this class
     *
     * @param DebuggerSeverity $severity The severity of the trace
     * @param string $title The title of the trace
     * @param string $message The message of the trace
     * @param string $file The name of the file the trace occurred in
     * @param int $line The line in the file the trace occurred on
     * @param array $steps Collection of steps in this trace
     * @param Trace|null $previous The previous trace
     */
    public function __construct(DebuggerSeverity $severity, string $title, string $message, string $file, int $line, array $steps, ?Trace $previous = null)
    {
        $this->severity = $severity;
        $this->title = $title;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->steps = array_map(function (array $step) {
            return new TraceStep($step);
        }, $steps);
        $this->previous = $previous;
    }

    /**
     * Get the severity of the trace
     *
     * @return DebuggerSeverity
     */
    public function getSeverity(): DebuggerSeverity
    {
        return $this->severity;
    }

    /**
     * Get the title of the trace
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the message of the trace
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the file the trace occurred in
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get the line in the file the trace occurred on
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Get the collection of steps in the trace
     *
     * @return array
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Get the previous trace
     *
     * @return Trace|null
     */
    public function getPrevious(): ?Trace
    {
        return $this->previous ?? null;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $result = '';
        if (strlen($this->title) > 0) {
            $result .= $this->title . "\n";
        }
        $result .= $this->message . "\n";
        $result .= '> in file ' . $this->file . ($this->line > 0 ? ':' . $this->line : '') . "\n";
        $result .= implode("\n", array_map(function (TraceStep $step) {
                return '^' . substr((string)$step, 1);
            }, $this->steps)) . "\n";
        if ($this->previous !== null) {
            $result .= '# ' . str_replace("\n", "\n# ", (string)$this->previous);
        }
        return trim($result);
    }
}