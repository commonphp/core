<?php


use CommonPHP\Core\Arrayifier;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\ServiceManager;
use PHPUnit\Framework\TestCase;

class ServiceManagerTest extends TestCase
{
    private Injector $injector;
    private Inspector $inspector;

    public function testAddService()
    {
        $serviceManager = $this->createServiceManager();
        $serviceManager->addService(Arrayifier::class, new Arrayifier($this->injector, $this->inspector));
        $this->assertTrue($serviceManager->hasService(Arrayifier::class));
    }

    private function createServiceManager(): ServiceManager
    {
        return new ServiceManager($this->injector);
    }

    public function testGetService()
    {
        $serviceManager = $this->createServiceManager();
        $arrayifier = $serviceManager->getService(Arrayifier::class);
        $newArrayifier = new Arrayifier($this->injector, $this->inspector);
        $this->assertSame($serviceManager->getService(Arrayifier::class), $arrayifier);
        $this->assertNotSame($serviceManager->getService(Arrayifier::class), $newArrayifier);
    }

    public function testIsService()
    {
        $serviceManager = $this->createServiceManager();
        $this->assertTrue($serviceManager->isService(Arrayifier::class));
        $this->assertFalse($serviceManager->isService(Exception::class));
    }

    public function testHasService()
    {
        $serviceManager = $this->createServiceManager();
        $serviceManager->addService(Arrayifier::class, new Arrayifier($this->injector, $this->inspector));
        $this->assertTrue($serviceManager->hasService(Arrayifier::class));
    }

    protected function setUp(): void
    {
        $this->injector = new Injector();
        $this->inspector = new Inspector();
    }
}
