<?php

namespace Attributes;

use CommonPHP\Core\Attributes\Service;
use Exception;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    public function testGetServiceInstance()
    {
        $service = $this->createServiceAttribute();
        $service->setServiceInstance($this);
        $this->assertEquals($service->getServiceInstance(), $this);
        $this->assertNotEquals($service->getServiceInstance(), new Exception());
    }

    private function createServiceAttribute(): Service
    {
        return new Service();
    }

    public function testSetServiceClass()
    {
        $service = $this->createServiceAttribute();
        $service->setServiceClass(get_class($this));
        $this->assertEquals($service->getServiceClass(), get_class($this));
    }

    public function testGetServiceClass()
    {
        $service = $this->createServiceAttribute();
        $service->setServiceClass(get_class($this));
        $this->assertEquals($service->getServiceClass(), get_class($this));
        $this->assertNotEquals($service->getServiceClass(), Exception::class);
    }

    public function testHasServiceInstance()
    {
        $service = $this->createServiceAttribute();
        $this->assertFalse($service->hasServiceInstance());
        $service->setServiceInstance($this);
        $this->assertTrue($service->hasServiceInstance());
    }

    public function testSetServiceInstance()
    {
        $service = $this->createServiceAttribute();
        $service->setServiceInstance($this);
        $this->assertEquals($service->getServiceInstance(), $this);
        $this->assertNotEquals($service->getServiceInstance(), new Exception());
    }
}
