<?php


use CommonPHP\Core\Enums\FileMode;
use CommonPHP\Core\Exceptions\FilesystemException;
use CommonPHP\Core\Filesystem;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    private string $root;
    private string $entry;

    public function testPutContents()
    {
        $filesystem = $this->createFilesystemObject();
        $content = 'test: ' . microtime(true);
        $filename = $filesystem->getFile('/var/temp/test.txt');
        $filesystem->putContents($filename, $content);
        $this->assertEquals(file_get_contents($filename), $content);
    }

    private function createFilesystemObject(): Filesystem
    {
        return new Filesystem($this->root, $this->entry);
    }

    public function testValidateFile()
    {
        $filesystem = $this->createFilesystemObject();
        try {
            $filesystem->validateFile('/composer.json', FileMode::None);
        } catch (FilesystemException $e) {
            $this->fail($e);
        }
        $this->assertTrue(true);
    }

    public function testGetFile()
    {
        $filesystem = $this->createFilesystemObject();
        $filename = $filesystem->getFile('/var/temp/test.txt');
        $this->assertEquals($filename, $this->root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'test.txt');
    }

    public function testGetRoot()
    {
        $filesystem = $this->createFilesystemObject();
        $this->assertEquals($filesystem->getRoot(), $this->root . DIRECTORY_SEPARATOR);
    }

    public function testAddNamespace()
    {
        $filesystem = $this->createFilesystemObject();
        $filesystem->addNamespace('temp', $filesystem->getDirectory('/var/temp'));
        $this->assertTrue($filesystem->hasNamespace('temp'));
    }

    public function testGetNamespace()
    {
        $filesystem = $this->createFilesystemObject();
        $filesystem->addNamespace('temp', $filesystem->getDirectory('/var/temp'));
        $this->assertEquals($filesystem->getNamespace('temp'), $this->root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
    }

    public function testGetEntryPoint()
    {
        $filesystem = $this->createFilesystemObject();
        $this->assertEquals($filesystem->getEntryPoint(), $this->entry);
    }

    public function testJsonEncode()
    {
        $filesystem = $this->createFilesystemObject();
        $filename = $filesystem->getFile('/var/temp/filesystem.json');
        $filesystem->jsonEncode($filename, ['test']);
        $this->assertEquals(file_get_contents($filename), "[\n    \"test\"\n]");
    }

    public function testHasNamespace()
    {
        $filesystem = $this->createFilesystemObject();
        $filesystem->addNamespace('temp', $filesystem->getDirectory('/var/temp'));
        $this->assertTrue($filesystem->hasNamespace('temp'));
    }

    public function testGetContents()
    {
        $filesystem = $this->createFilesystemObject();
        $filename = $filesystem->getFile('/var/temp/filesystem.json');
        $this->assertEquals($filesystem->getContents($filename), "[\n    \"test\"\n]");
    }

    public function testJsonDecode()
    {
        $filesystem = $this->createFilesystemObject();
        $filename = $filesystem->getFile('/var/temp/filesystem.json');
        $this->assertEquals($filesystem->jsonDecode($filename), ["test"]);
    }

    public function testValidateDirectory()
    {
        $filesystem = $this->createFilesystemObject();
        try {
            $filesystem->validateDirectory('/var/temp', FileMode::None);
        } catch (FilesystemException $e) {
            $this->fail($e);
        }
        $this->assertTrue(true);
    }

    public function testValidatePath()
    {
        $filesystem = $this->createFilesystemObject();
        try {
            $filesystem->validatePath('/var/temp', FileMode::None);
        } catch (FilesystemException $e) {
            $this->fail($e);
        }
        $this->assertTrue(true);
    }

    public function testGetDirectory()
    {
        $filesystem = $this->createFilesystemObject();
        $this->assertEquals($filesystem->getDirectory('/var/temp'), $this->root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
    }

    public function testGetPath()
    {
        $filesystem = $this->createFilesystemObject();
        $this->assertEquals($filesystem->getPath('/var/temp'), $this->root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'temp');
    }

    protected function setUp(): void
    {
        $this->root = dirname(__FILE__, 2);
        $this->entry = __FILE__;
    }
}
