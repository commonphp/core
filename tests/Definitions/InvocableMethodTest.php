<?php

namespace Definitions;

use CommonPHP\Core\Definitions\InvocableMethod;
use CommonPHP\Core\Injector;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InvocableMethodTest extends TestCase
{
    private Injector $injector;

    public function testInvoke()
    {
        $invocableMethod = $this->createInvocableMethod();
        $rand = rand(0, 1024 * 1024);
        $this->assertEquals($invocableMethod->invoke($this->injector, $this, ['rand' => $rand]), $rand);

    }

    private function createInvocableMethod(): InvocableMethod
    {
        return new InvocableMethod(new ReflectionClass($this), 'targetMethod');
    }

    public function testGetMethod()
    {
        $invocableMethod = $this->createInvocableMethod();
        $this->assertEquals($invocableMethod->getMethod(), 'targetMethod');
    }

    public function testGetClass()
    {
        $invocableMethod = $this->createInvocableMethod();
        $this->assertEquals($invocableMethod->getClass(), get_class($this));
    }

    protected function setUp(): void
    {
        $this->injector = new Injector();
    }

    private function targetMethod(int $rand): int
    {
        return $rand;
    }
}
