<?php

namespace Definitions;

use CommonPHP\Core\Definitions\Trace;
use CommonPHP\Core\Enums\DebuggerSeverity;
use PHPUnit\Framework\TestCase;

class TraceTest extends TestCase
{
    private Trace $trace;

    public function test__toString()
    {
        $this->assertEquals((string)$this->trace, "_TITLE_\n_MESSAGE_\n> in file _FILE_:23\n^ at _CLASS_>TEST>_FUNCTION_ in file _FILE_:42", (string)$this->trace);
    }

    public function testGetTitle()
    {
        $this->assertEquals($this->trace->getTitle(), '_TITLE_', $this->trace->getTitle());
    }

    public function testGetSteps()
    {
        $this->assertEquals((string)$this->trace->getSteps()[0], '> at _CLASS_>TEST>_FUNCTION_ in file _FILE_:42', (string)$this->trace->getSteps()[0]);
    }

    public function testGetPrevious()
    {
        $this->assertEquals($this->trace->getPrevious(), null, (string)$this->trace->getPrevious());
    }

    public function testGetSeverity()
    {
        $this->assertEquals($this->trace->getSeverity(), DebuggerSeverity::Application, $this->trace->getSeverity()->name);
    }

    public function testGetFile()
    {
        $this->assertEquals($this->trace->getFile(), '_FILE_', $this->trace->getFile());
    }

    public function testGetMessage()
    {
        $this->assertEquals($this->trace->getMessage(), '_MESSAGE_', $this->trace->getMessage());
    }

    public function testGetLine()
    {
        $this->assertEquals($this->trace->getLine(), 23, $this->trace->getLine());
    }

    protected function setUp(): void
    {
        $this->trace = new Trace(DebuggerSeverity::Application, '_TITLE_', '_MESSAGE_', '_FILE_', 23, [
            [
                'function' => '_FUNCTION_',
                'line' => 42,
                'file' => '_FILE_',
                'class' => '_CLASS_',
                'object' => $this,
                'type' => '>TEST>',
                'args' => ['arg1', 'arg2'],
                'signature' => '_SIGNATURE_'
            ]
        ], null);
    }
}
