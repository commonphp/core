<?php

namespace Definitions;

use CommonPHP\Core\Definitions\TraceStep;
use PHPUnit\Framework\TestCase;

class TraceStepTest extends TestCase
{
    private TraceStep $step;

    public function testGetObject()
    {
        $this->assertEquals(get_class($this->step->getObject()), self::class, get_class($this->step->getObject()));
    }

    public function test__toString()
    {
        $this->assertEquals((string)$this->step, '> at _CLASS_>TEST>_FUNCTION_ in file _FILE_:42', (string)$this->step);
    }

    public function testGetLine()
    {
        $this->assertEquals($this->step->getLine(), 42, $this->step->getLine());
    }

    public function testGetSignature()
    {
        $this->assertEquals($this->step->getSignature(), '_CLASS_>TEST>_FUNCTION_', $this->step->getSignature());
    }

    public function testGetFunction()
    {
        $this->assertEquals($this->step->getFunction(), '_FUNCTION_', $this->step->getFunction());
    }

    public function testGetFile()
    {
        $this->assertEquals($this->step->getFile(), '_FILE_', $this->step->getFile());
    }

    public function testGetClass()
    {
        $this->assertEquals($this->step->getClass(), '_CLASS_', $this->step->getClass());
    }

    public function testGetType()
    {
        $this->assertEquals($this->step->getType(), '>TEST>', $this->step->getType());
    }

    public function testGetArguments()
    {
        $this->assertEquals(json_encode($this->step->getArguments()), '["arg1","arg2"]', json_encode($this->step->getArguments()));
    }

    protected function setUp(): void
    {
        $this->step = new TraceStep([
            'function' => '_FUNCTION_',
            'line' => 42,
            'file' => '_FILE_',
            'class' => '_CLASS_',
            'object' => $this,
            'type' => '>TEST>',
            'args' => ['arg1', 'arg2'],
            'signature' => '_SIGNATURE_'
        ]);
    }
}
