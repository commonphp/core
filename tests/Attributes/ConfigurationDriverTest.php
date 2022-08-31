<?php

namespace Attributes;

use CommonPHP\Core\Attributes\ConfigurationDriver;
use PHPUnit\Framework\TestCase;

class ConfigurationDriverTest extends TestCase
{

    public function testGetPattern()
    {
        $test = new ConfigurationDriver('/^[a-z]$/ix');
        $this->assertNotEquals('/^[a-z$/ix', $test->getPattern(), 'Configurable->getName() === \'/^[a-z$/ix\'');
        $test = new ConfigurationDriver('/^.*$/ix');
        $this->assertEquals('/^.*$/ix', $test->getPattern(), 'Configurable->getName() === \'/^.*$/ix\'');
    }
}
