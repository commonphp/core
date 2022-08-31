<?php

namespace Collections;

use CommonPHP\Core\Collections\ConfigurationDriverCollection;
use CommonPHP\Core\Debugger;
use CommonPHP\Core\Drivers\JsonConfigurationDriver;
use CommonPHP\Core\Filesystem;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\ServiceManager;
use PHPUnit\Framework\TestCase;

class ConfigurationDriverCollectionTest extends TestCase
{
    private Injector $injector;
    private Debugger $debugger;
    private ServiceManager $serviceManager;
    private Filesystem $filesystem;

    public function testFind()
    {
        $target = new ConfigurationDriverCollection($this->injector, $this->debugger);
        $target->load(JsonConfigurationDriver::class);
        $this->assertNotEquals(false, $target->find('test.json', false), 'test.json');
        $this->assertEquals(false, $target->find('test.ini', false), 'test.ini');
    }

    public function testLoad()
    {
        $target = new ConfigurationDriverCollection($this->injector, $this->debugger);
        $target->load(JsonConfigurationDriver::class);
        $this->assertTrue($target->has(JsonConfigurationDriver::class), 'target->has() ' . JsonConfigurationDriver::class);
    }

    protected function setUp(): void
    {
        $this->injector = new Injector();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->serviceManager = $this->injector->instantiate(ServiceManager::class);
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->filesystem = $this->serviceManager->getService(Filesystem::class);
        $this->filesystem->addNamespace('config', dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config');
        $this->debugger = new Debugger(false);
    }
}
