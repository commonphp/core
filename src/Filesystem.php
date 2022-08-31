<?php

/**
 * Filesystem Component from the CommonPHP Core Library
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @link https://commonphp.org/core
 * @version 1.0
 * @license GPL 3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @package commonphp\core
 */

namespace CommonPHP\Core;

use CommonPHP\Core\Attributes\Service;
use CommonPHP\Core\Enums\FileMode;
use CommonPHP\Core\Exceptions\FilesystemException;

/**
 * Simplifies Filesystem access
 */
#[Service]
final class Filesystem
{
    /** @var string The root path for the application */
    private string $root;

    /** @var string The entry point for the application */
    private string $entryPoint;

    /** @var array Filesystem namespaces, also referred to as virtual paths or directories */
    private array $namespaces = [];

    /**
     * Instantiate this class
     *
     * @param string|null $root The root path for the application (null will try to detect)
     * @param string|null $entryPoint The entry point for the application (null will try to detect)
     * @throws FilesystemException
     */
    public function __construct(?string $root = null, ?string $entryPoint = null)
    {
        if ($entryPoint === null) $entryPoint = $_SERVER['SCRIPT_FILENAME'];
        $entryPoint = realpath($entryPoint);
        if ($entryPoint === false) {
            throw new FilesystemException('The entry point was not a valid file');
        }
        if ($root === null) $root = dirname($entryPoint, 2);
        if (!str_ends_with($root, DIRECTORY_SEPARATOR)) $root .= DIRECTORY_SEPARATOR;
        $this->entryPoint = $entryPoint;
        $this->root = $root;
    }

    /**
     * Get the root path for the application
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * Get the entry point for the application
     *
     * @return string
     */
    public function getEntryPoint(): string
    {
        return $this->entryPoint;
    }

    /**
     * Add a filesystem namespace
     *
     * @param string $namespace The namespace to add
     * @param string $path The real path for the namespace (can exist outside the root path)
     * @return void
     * @throws FilesystemException
     */
    public function addNamespace(string $namespace, string $path): void
    {
        $this->validateNamespace($namespace);
        if ($this->hasNamespace($namespace)) {
            throw new FilesystemException('Namespace \'' . $namespace . '\' already exists');
        }
        if (!str_ends_with($path, DIRECTORY_SEPARATOR)) $path .= DIRECTORY_SEPARATOR;
        $this->validateDirectory($path, FileMode::ReadWrite);
        $this->namespaces[$namespace] = $path;
    }

    /**
     * Validate a namespace name
     *
     * @param string $namespace The namespace name to validate
     * @return void
     * @throws FilesystemException
     */
    private function validateNamespace(string $namespace): void
    {
        if (!preg_match('/^[a-z][a-z0-9_\-]*$/', $namespace)) {
            throw new FilesystemException('Namespaces must start with a letter and contain only letters, numbers, underscores and hyphens');
        }
    }

    /**
     * Check if a namespace has been set
     *
     * @param string $namespace The namespace to check for
     * @return bool
     */
    public function hasNamespace(string $namespace): bool
    {
        return array_key_exists($namespace, $this->namespaces);
    }

    /**
     * Validate a directory
     *
     * @param string $path The absolute path of the directory
     * @param FileMode $mode The anticipated access mode for the directory
     * @return void
     * @throws FilesystemException
     */
    public function validateDirectory(string $path, FileMode $mode): void
    {
        $this->validatePath($path, $mode);
        if (($mode == FileMode::Read || $mode == FileMode::ReadWrite) && !is_dir($path)) {
            throw new FilesystemException($path . ' is not a directory');
        }
    }

    /**
     * Validate a path
     *
     * @param string $path The absolute path to validate
     * @param FileMode $mode The anticipated access mode for the path
     * @return void
     * @throws FilesystemException
     */
    public function validatePath(string $path, FileMode $mode): void
    {
        if ($mode == FileMode::Read || $mode === FileMode::ReadWrite) {
            if (!file_exists($path)) {
                throw new FilesystemException($path . ' does not exist');
            }
            if (!is_readable($path)) {
                throw new FilesystemException($path . ' is not readable');
            }
        }
        if ($mode === FileMode::Write || $mode === FileMode::ReadWrite) {
            if (file_exists($path) && !is_writable($path)) {
                throw new FilesystemException($path . ' is readonly');
            }
            if (!file_exists($path) && file_exists(dirname($path)) && !is_writable(dirname($path))) {
                throw new FilesystemException($path . ' does not exist and it\'s parent directory is readonly');
            }
            if (!file_exists($path) && !file_exists(dirname($path))) {
                throw new FilesystemException($path . ' nor it\'s parent directory exists.');
            }
        }
    }

    /**
     * Get the absolute directory path
     *
     * @param string $virtualPath The virtual path to get the absolute path from
     * @return string
     * @throws FilesystemException
     */
    public function getDirectory(string $virtualPath): string
    {
        $path = $this->getPath($virtualPath);
        if (!str_ends_with($path, DIRECTORY_SEPARATOR)) $path .= DIRECTORY_SEPARATOR;
        return $path;
    }

    /**
     * Get the absolute path
     *
     * @param string|null $virtualPath The virtual path to get the absolute path from
     * @return string
     * @throws FilesystemException
     */
    public function getPath(?string $virtualPath = null): string
    {
        $result = $this->root;
        if ($virtualPath !== null) {
            if (DIRECTORY_SEPARATOR != '/') $virtualPath = str_replace('/', DIRECTORY_SEPARATOR, $virtualPath);
            if (str_starts_with($virtualPath, '@')) {
                $namespace = trim(substr($virtualPath, 0, str_contains($virtualPath, DIRECTORY_SEPARATOR) ? strpos($virtualPath, DIRECTORY_SEPARATOR) : strlen($virtualPath)), '\\/@');
                $virtualPath = substr($virtualPath, strlen($namespace) + 2);
                $result = $this->getNamespace($namespace);
            }
            $result .= ltrim($virtualPath, '\\/');
        }
        return $result;
    }

    /**
     * Get the namespace path
     *
     * @param string $namespace The namespace to get the path of
     * @return string
     * @throws FilesystemException
     */
    public function getNamespace(string $namespace): string
    {
        if (!$this->hasNamespace($namespace)) {
            throw new FilesystemException('Namespace \'' . $namespace . '\' does not exist');
        }
        return $this->namespaces[$namespace];
    }

    /**
     * Get the absolute path of a file
     *
     * @param string $virtualPath The virtual path to get the absolute filename from
     * @return string
     * @throws FilesystemException
     */
    public function getFile(string $virtualPath): string
    {
        $path = $this->getPath($virtualPath);
        return rtrim($path, '\\/');
    }

    /**
     * Decode JSON from the specified absolute filename
     *
     * @param string $realPath The absolute filename to read
     * @return array
     * @throws FilesystemException
     */
    public function jsonDecode(string $realPath): array
    {
        $json = $this->getContents($realPath);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new FilesystemException($realPath . ' does not contain a valid JSON array structure');
        }
        return $data;
    }

    /**
     * Get the contents of the specified absolute filename
     *
     * @param string $realPath The absolute filename to read
     * @return string
     * @throws FilesystemException
     */
    public function getContents(string $realPath): string
    {
        $this->validateFile($realPath, FileMode::Read);
        $data = file_get_contents($realPath);
        if (!is_string($data)) {
            throw new FilesystemException('Could not read file content from ' . $realPath);
        }
        return $data;
    }

    /**
     * Validate a file
     *
     * @param string $path The absolute path of the file
     * @param FileMode $mode The anticipated access mode for the file
     * @return void
     * @throws FilesystemException
     */
    public function validateFile(string $path, FileMode $mode): void
    {
        $this->validatePath($path, $mode);
        if (($mode == FileMode::Read || $mode == FileMode::ReadWrite) && !is_file($path)) {
            throw new FilesystemException($path . ' is not a file');
        }
    }

    /**
     * Encode an array into an absolute file
     *
     * @param string $realPath The absolute path of the file
     * @param array $data The data to encode
     * @param bool $pretty Should the output be pretty?
     * @return void
     * @throws FilesystemException
     */
    public function jsonEncode(string $realPath, array $data, bool $pretty = true): void
    {
        $json = json_encode($data, $pretty ? JSON_PRETTY_PRINT : 0);
        if (!is_string($json)) {
            throw new FilesystemException('Could not encode JSON data for ' . $realPath);
        }
        $this->putContents($realPath, $json);
    }

    /**
     * Put supplied contents into an absolute file
     *
     * @param string $realPath The absolute path of the file
     * @param string $data The data to write
     * @return void
     * @throws FilesystemException
     */
    public function putContents(string $realPath, string $data): void
    {
        $this->validateFile($realPath, FileMode::Write);
        if (file_put_contents($realPath, $data) === false) {
            throw new FilesystemException('Could not write file content to ' . $realPath);
        }
    }
}