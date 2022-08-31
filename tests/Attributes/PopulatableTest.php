<?php

namespace Attributes;

use CommonPHP\Core\Attributes\Populatable;
use PHPUnit\Framework\TestCase;

class PopulatableTest extends TestCase
{
    private Populatable $noMethod;
    private Populatable $hasMethod;

    public function testGetMethod()
    {
        $this->assertFalse($this->noMethod->hasMethod(), 'noMethod->hasMethod()');
        $this->assertTrue($this->hasMethod->hasMethod(), 'hasMethod->hasMethod()');
    }

    public function testHasMethod()
    {
        $this->assertNull($this->noMethod->getMethod(), 'noMethod->getMethod()');
        $this->assertEquals($this->hasMethod->getMethod(), 'testMethod', 'hasMethod->getMethod()');
    }

    protected function setUp(): void
    {
        $this->noMethod = new Populatable();
        $this->hasMethod = new Populatable('testMethod');
    }
}
