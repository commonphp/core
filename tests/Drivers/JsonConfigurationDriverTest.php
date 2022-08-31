<?php

namespace Drivers;

use CommonPHP\Core\Drivers\JsonConfigurationDriver;
use CommonPHP\Core\Filesystem;
use PHPUnit\Framework\TestCase;

class JsonConfigurationDriverTest extends TestCase
{
    private Filesystem $filesystem;

    private JsonConfigurationDriver $driver;

    public function testWrite()
    {
        $time = round(microtime(true), 4);
        $filename = 'test.driver-' . $time . '.json';
        $this->driver->write($filename, ['test' => $time]);

        $filename = $this->filesystem->getFile('@config/' . $filename);
        $this->assertTrue(file_exists($filename), 'file_exists');
        $this->assertEquals(file_get_contents($filename), "{\n    \"test\": " . $time . "\n}", 'comparison');
    }

    public function testRead()
    {
        $this->driver->write('test.json', ['int' => 2353]);
        $data = $this->driver->read('test.json');
        $this->assertTrue(array_key_exists('int', $data), 'test.json:int exists');
        $this->assertEquals($data['int'], 2353, 'test.json:int = 2353');
    }

    public function testExists()
    {
        $this->driver->write('test.json', ['int' => 2353]);
        $this->assertTrue($this->driver->exists('test.json'), 'test.json exists');
        $this->assertFalse($this->driver->exists('missing.json'), 'missing.json not exists');
    }

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem(dirname(__FILE__, 3), __FILE__);
        $this->filesystem->addNamespace('config', dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config');
        $this->driver = new JsonConfigurationDriver($this->filesystem);
    }
}
