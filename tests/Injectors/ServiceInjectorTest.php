<?php

namespace Injectors;

use CommonPHP\Core\Injector;
use CommonPHP\Core\Injectors\ServiceInjector;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\ServiceManager;
use Exception;
use PHPUnit\Framework\TestCase;

class ServiceInjectorTest extends TestCase
{
    private Injector $injector;
    private Inspector $inspector;

    public function testGet()
    {
        $injector = $this->createServiceInjector();
        $newInjector = new Injector();
        $this->assertSame($injector->get(Injector::class, 'INJECTOR'), $this->injector);
        $this->assertSame($injector->get(Inspector::class, 'INSPECTOR'), $this->inspector);
        $this->assertNotSame($injector->get(Injector::class, 'NEWINJECTOR'), $newInjector);
    }

    private function createServiceInjector(): ServiceInjector
    {
        return new ServiceInjector($this->createServiceManager());
    }

    private function createServiceManager(): ServiceManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->injector->instantiate(ServiceManager::class);
    }

    public function testCheck()
    {
        $injector = $this->createServiceInjector();
        $this->assertTrue($injector->check(Inspector::class, false));
        $this->assertFalse($injector->check(Exception::class, false));
    }

    protected function setUp(): void
    {
        $this->injector = new Injector();
        $this->inspector = $this->injector->getInspector();
    }
}
