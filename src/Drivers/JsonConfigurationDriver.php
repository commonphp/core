<?php

/**
 * Support Class for Configuration Component
 *
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @since 1.0
 *
 * @see \CommonPHP\Core\Configuration
 */

namespace CommonPHP\Core\Drivers;

use CommonPHP\Core\Attributes\ConfigurationDriver;
use CommonPHP\Core\Contracts\ConfigurationDriverContract;
use CommonPHP\Core\Enums\FileMode;
use CommonPHP\Core\Exceptions\ConfigurationException;
use CommonPHP\Core\Exceptions\FilesystemException;
use CommonPHP\Core\Filesystem;

/**
 * Provides configuration functionality for reading a JSON file
 */
#[ConfigurationDriver('/^.*\.json$/ix')]
final class JsonConfigurationDriver implements ConfigurationDriverContract
{
    /** @var Filesystem The Filesystem component */
    private Filesystem $filesystem;

    /**
     * Instantiate this class
     *
     * @param Filesystem $filesystem The Filesystem component
     * @throws ConfigurationException
     */
    public function __construct(Filesystem $filesystem)
    {
        if (!$filesystem->hasNamespace('config')) {
            throw new ConfigurationException('`config` namespace in the Filesystem must be set to use the JSON configuration driver');
        }
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritDoc
     * @throws ConfigurationException
     */
    public function read(string $name): array
    {
        try {
            return $this->filesystem->jsonDecode($this->filesystem->getFile('@config/' . ltrim($name, '\\/')));
        } catch (FilesystemException $e) {
            throw new ConfigurationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     * @throws ConfigurationException
     */
    public function write(string $name, array $data): void
    {
        try {
            $this->filesystem->jsonEncode($this->filesystem->getFile('@config/' . ltrim($name, '\\/')), $data);
        } catch (FilesystemException $e) {
            throw new ConfigurationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $name): bool
    {
        try {
            $this->filesystem->validateFile($this->filesystem->getFile('@config/' . ltrim($name, '\\/')), FileMode::Read);
        } catch (FilesystemException) {
            return false;
        }
        return true;
    }
}