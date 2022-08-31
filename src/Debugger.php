<?php

/**
 * Debugger Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Definitions\Trace;
use CommonPHP\Core\Enums\DebuggerSeverity;
use CommonPHP\Core\Exceptions\DebuggerException;
use DateTime;
use Throwable;

/**
 * Provide basic debugging and logging functionality
 */
#[Service]
final class Debugger
{
    /** @var string The custom log save path */
    private string $customLogPath;

    /** @var string[][] Messages that have occurred during this request */
    private array $messages = [];

    /**
     * Instantiate this class
     *
     * @param bool $registerHandlers Should the error and exception handlers be registered?
     */
    public function __construct(bool $registerHandlers = true)
    {
        if (!$registerHandlers) return;
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Get the custom log save path
     *
     * @return string|null
     */
    public function getCustomLogPath(): ?string
    {
        return $this->customLogPath ?? null;
    }

    /**
     * Set the custom log save path
     *
     * @param string $customLogPath The custom log save path
     * @return void
     * @throws DebuggerException
     */
    public function setCustomLogPath(string $customLogPath): void
    {
        if (isset($this->customLogPath)) throw new DebuggerException('The custom path for the Debugger has already been set');
        if (!str_ends_with($customLogPath, DIRECTORY_SEPARATOR)) $customLogPath .= DIRECTORY_SEPARATOR;
        $this->customLogPath = $customLogPath;
        $this->rotateLogs($customLogPath);
    }

    /**
     * Rotate the logs, archiving those that exceed 1MB
     *
     * @param string $path The log path to rotate in
     * @return void
     */
    private function rotateLogs(string $path)
    {
        foreach (DebuggerSeverity::cases() as $case) {
            $fileName = $path . strtolower($case->name) . '.log';
            if (file_exists($fileName) && filesize($fileName) > 1048576) {
                $newFileName = substr($fileName, 0, strlen($fileName) - 4) . '.' . microtime(true) . '.log';
                rename($fileName, $newFileName);
            }
        }
    }

    /**
     * Get the trace messages of the defined severity
     *
     * @param DebuggerSeverity|null $severity The trace severity
     * @return array
     */
    public function getMessages(?DebuggerSeverity $severity = null): array
    {
        if ($severity === null) {
            return $this->messages;
        }
        return array_key_exists($severity->name, $this->messages) ? $this->messages[$severity->name] : [];
    }

    /**
     * Handle an error, typically called as a result of trigger_error
     *
     * @param int $severity The PHP error severity
     * @param string $message The error message
     * @param string $file The file that the error occurred in
     * @param int $line The lin that the error occurred on
     * @return void
     */
    public function handleError(int $severity, string $message, string $file, int $line): void
    {
        $title = $this->getErrorSeverityTitle($severity);
        $severity = $this->convertErrorSeverity($severity);
        $this->logError($severity, $title, $message, $file, $line, debug_backtrace());
    }

    /**
     * Convert the PHP error severity (code) to a string
     *
     * @param int $severity The PHP error severity
     * @return string
     */
    private function getErrorSeverityTitle(int $severity): string
    {
        return match ($severity) {
            E_ERROR => 'PHP Error',
            E_WARNING => 'PHP Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'PHP Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'Error',
            E_USER_WARNING => 'Warning',
            E_USER_NOTICE => 'Notice',
            E_STRICT => 'Strict Error',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'PHP Deprecation Warning',
            E_USER_DEPRECATED => 'Deprecation Warning',
            default => 'Unknown Error'
        };
    }

    /**
     * Convert the PHP error severity (code) to a DebuggerSeverity
     *
     * @param int $severity The PHP error severity
     * @return DebuggerSeverity
     */
    private function convertErrorSeverity(int $severity): DebuggerSeverity
    {
        return match ($severity) {
            E_NOTICE, E_USER_NOTICE => DebuggerSeverity::Notice,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING, E_DEPRECATED, E_USER_DEPRECATED => DebuggerSeverity::Warning,
            default => DebuggerSeverity::Error
        };
    }

    /**
     * Log an error with custom properties
     *
     * @param DebuggerSeverity $severity The error severity
     * @param string $title The title of the error
     * @param string $message The error message
     * @param string $file The file the error occurred on
     * @param int $line The line the error occurred on
     * @param array|null $trace The trace (usually provided by debug_backtrace())
     * @return void
     */
    public function logError(DebuggerSeverity $severity, string $title, string $message, string $file, int $line, ?array $trace = null): void
    {
        if ($trace === null) $trace = debug_backtrace();
        $this->logTrace(new Trace($severity, $title, $message, $file, $line, $trace));
    }

    /**
     * Log a Trace object
     *
     * @param Trace $trace The trace to log
     * @return void
     */
    public function logTrace(Trace $trace): void
    {
        $this->log((string)$trace, $trace->getSeverity());
    }

    /**
     * Log a message
     *
     * @param string $message The message to log
     * @param DebuggerSeverity $severity The message severity
     * @return void
     */
    public function log(string $message, DebuggerSeverity $severity = DebuggerSeverity::Info): void
    {
        if (!array_key_exists($severity->name, $this->messages)) {
            $this->messages[$severity->name] = [];
        }
        $this->messages[$severity->name][] = $message;
        if (php_sapi_name() === "cli") {
            echo "\e[1;31m" . $message . "\e[0m\n";
            return;
        }
        if (!isset($this->customLogPath)) {
            error_log($message);
            return;
        }
        $filename = $this->customLogPath . strtolower($severity->name) . '.log';
        if (!file_exists($filename)) {
            touch($filename);
        }
        $stamp = '[' . (new DateTime())->format('c') . '] ';
        $spacer = str_repeat(' ', strlen($stamp));
        $message = $stamp . str_replace("\n", "\n" . $spacer, $message) . "\n";
        file_put_contents($filename, $message, FILE_APPEND);
    }

    /**
     * Handle Exception, usually called from the PHP exception handler
     *
     * @param Throwable $exception The exception to handle
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        $this->logException($exception, DebuggerSeverity::Error);
    }

    /**
     * Log an exception
     *
     * @param Throwable $exception The exception to log
     * @param DebuggerSeverity $severity The exception severity
     * @return void
     */
    public function logException(Throwable $exception, DebuggerSeverity $severity = DebuggerSeverity::Warning): void
    {
        $this->logTrace($this->generateTraceFromException($exception, $severity));
    }

    /**
     * Convert an exception to a trace
     *
     * @param Throwable $exception The exception to convert
     * @param DebuggerSeverity $severity The exception severity
     * @return Trace
     */
    private function generateTraceFromException(Throwable $exception, DebuggerSeverity $severity): Trace
    {
        return new Trace(
            $severity,
            'Unhandled ' . get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTrace(),
            $exception->getPrevious() === null ? null : $this->generateTraceFromException($exception->getPrevious(), $severity)
        );
    }
}