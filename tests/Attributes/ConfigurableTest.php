<?php

namespace Attributes;

use CommonPHP\Core\Attributes\Configurable;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    public function testGetName()
    {
        $test = new Configurable('testName');
        $this->assertEquals('testName', $test->getName(), 'Configurable->getName()');
    }
}
