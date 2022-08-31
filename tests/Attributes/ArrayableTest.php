<?php

namespace Attributes;

use CommonPHP\Core\Attributes\Arrayable;
use PHPUnit\Framework\TestCase;

class ArrayableTest extends TestCase
{
    private Arrayable $noMethod;
    private Arrayable $hasMethod;

    public function testHasMethod()
    {
        $this->assertFalse($this->noMethod->hasMethod(), 'noMethod->hasMethod()');
        $this->assertTrue($this->hasMethod->hasMethod(), 'hasMethod->hasMethod()');
    }

    public function testGetMethod()
    {
        $this->assertNull($this->noMethod->getMethod(), 'noMethod->getMethod()');
        $this->assertEquals($this->hasMethod->getMethod(), 'testMethod', 'hasMethod->getMethod()');
    }

    protected function setUp(): void
    {
        $this->noMethod = new Arrayable();
        $this->hasMethod = new Arrayable('testMethod');
    }
}
