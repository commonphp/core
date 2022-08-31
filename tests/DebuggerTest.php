<?php


use CommonPHP\Core\Debugger;
use CommonPHP\Core\Definitions\Trace;
use CommonPHP\Core\Enums\DebuggerSeverity;
use PHPUnit\Framework\TestCase;

class DebuggerTest extends TestCase
{
    private Debugger $debugger;
    private string $logPath;

    public function testLogTrace()
    {
        ob_start();
        $this->debugger->logTrace(new Trace(DebuggerSeverity::Security, '_TITLE_', '_MESSAGE_', '_FILE_', 23, [
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
        ], null));
        $data = substr(trim(ob_get_clean(), "\n\e"), 6);
        $data = substr($data, 0, strlen($data) - 4);
        $compare = "_TITLE_\n_MESSAGE_\n> in file _FILE_:23\n^ at _CLASS_>TEST>_FUNCTION_ in file _FILE_:42";
        $this->assertEquals($data, $compare, __FUNCTION__);
    }

    public function testSetCustomPath()
    {
        $debugger = new Debugger(false);
        $debugger->setCustomLogPath($this->logPath);
        $this->assertEquals($debugger->getCustomLogPath(), $this->logPath, __FUNCTION__);
    }

    public function testHandleException()
    {
        ob_start();
        $this->debugger->handleException(new Exception('test'));
        $data = substr(trim(ob_get_clean(), "\n\e"), 6);
        $data = substr($data, 0, strlen($data) - 4);
        $compare = str_replace('__LINE__', __LINE__ - 3, "Unhandled Exception\ntest\n> in file D:\\Projects\\qft-cphp-cjs\\common\\core\\tests\\DebuggerTest.php:__LINE__\n^ at DebuggerTest->testHandleException in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:1545\n^ at PHPUnit\\Framework\\TestCase->runTest in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:1151\n^ at PHPUnit\\Framework\\TestCase->runBare in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestResult.php:726\n^ at PHPUnit\\Framework\\TestResult->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:903\n^ at PHPUnit\\Framework\\TestCase->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php:672\n^ at PHPUnit\\Framework\\TestSuite->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php:672\n^ at PHPUnit\\Framework\\TestSuite->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php:673\n^ at PHPUnit\\TextUI\\TestRunner->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\Command.php:143\n^ at PHPUnit\\TextUI\\Command->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\Command.php:96\n^ at PHPUnit\\TextUI\\Command::main in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\phpunit:98");
        $this->assertEquals($data, $compare, __FUNCTION__);
    }

    public function testGetMessages()
    {
        ob_start();
        foreach (DebuggerSeverity::cases() as $case) {
            $this->debugger->log($case->name, $case);
        }
        ob_get_clean();

        $all = [];

        foreach (DebuggerSeverity::cases() as $case) {
            $all[$case->name] = $this->debugger->getMessages($case);
            $this->assertEquals(json_encode($this->debugger->getMessages($case)), '["' . $case->name . '"]', __FUNCTION__);
        }
        $this->assertEquals(json_encode($this->debugger->getMessages()), json_encode($all), __FUNCTION__);
    }

    public function testLog()
    {
        ob_start();
        $this->debugger->log('test', DebuggerSeverity::Debug);
        $data = substr(trim(ob_get_clean(), "\n\e"), 6);
        $data = substr($data, 0, strlen($data) - 4);
        $compare = "test";
        $this->assertEquals($data, $compare, __FUNCTION__);
    }

    public function testGetCustomPath()
    {
        $debugger = new Debugger(false);
        $debugger->setCustomLogPath($this->logPath);
        $this->assertEquals($debugger->getCustomLogPath(), $this->logPath, __FUNCTION__);
    }

    public function testLogError()
    {
        ob_start();
        $this->debugger->logError(DebuggerSeverity::Application, '_TITLE_', '_MESSAGE_', '_FILE_', 42, []);
        $data = substr(trim(ob_get_clean(), "\n\e"), 6);
        $data = substr($data, 0, strlen($data) - 4);
        $compare = "_TITLE_\n_MESSAGE_\n> in file _FILE_:42";
        $this->assertEquals($data, $compare, __FUNCTION__);
    }

    public function testHandleError()
    {
        ob_start();
        $this->debugger->handleError(E_CORE_ERROR, 'test', '__FILE__', 42);
        $data = substr(trim(ob_get_clean(), "\n\e"), 6);
        $data = substr($data, 0, strlen($data) - 4);
        $compare = str_replace('__LINE__', __LINE__ - 3, "Core Error\ntest\n> in file __FILE__:42\n^ at CommonPHP\\Core\\Debugger->handleError in file D:\\Projects\\qft-cphp-cjs\\common\\core\\tests\\DebuggerTest.php:__LINE__\n^ at DebuggerTest->testHandleError in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:1545\n^ at PHPUnit\\Framework\\TestCase->runTest in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:1151\n^ at PHPUnit\\Framework\\TestCase->runBare in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestResult.php:726\n^ at PHPUnit\\Framework\\TestResult->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:903\n^ at PHPUnit\\Framework\\TestCase->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php:672\n^ at PHPUnit\\Framework\\TestSuite->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php:672\n^ at PHPUnit\\Framework\\TestSuite->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php:673\n^ at PHPUnit\\TextUI\\TestRunner->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\Command.php:143\n^ at PHPUnit\\TextUI\\Command->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\Command.php:96\n^ at PHPUnit\\TextUI\\Command::main in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\phpunit:98");
        $this->assertEquals($data, $compare, __FUNCTION__);
    }

    public function testLogException()
    {
        ob_start();
        $this->debugger->logException(new Exception('test'), DebuggerSeverity::Notice);
        $data = substr(trim(ob_get_clean(), "\n\e"), 6);
        $data = substr($data, 0, strlen($data) - 4);
        $compare = str_replace('__LINE__', __LINE__ - 3, "Unhandled Exception\ntest\n> in file D:\\Projects\\qft-cphp-cjs\\common\\core\\tests\\DebuggerTest.php:__LINE__\n^ at DebuggerTest->testLogException in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:1545\n^ at PHPUnit\\Framework\\TestCase->runTest in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:1151\n^ at PHPUnit\\Framework\\TestCase->runBare in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestResult.php:726\n^ at PHPUnit\\Framework\\TestResult->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestCase.php:903\n^ at PHPUnit\\Framework\\TestCase->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php:672\n^ at PHPUnit\\Framework\\TestSuite->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\Framework\\TestSuite.php:672\n^ at PHPUnit\\Framework\\TestSuite->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\TestRunner.php:673\n^ at PHPUnit\\TextUI\\TestRunner->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\Command.php:143\n^ at PHPUnit\\TextUI\\Command->run in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\src\\TextUI\\Command.php:96\n^ at PHPUnit\\TextUI\\Command::main in file D:\\Projects\\qft-cphp-cjs\\common\\core\\vendor\\phpunit\\phpunit\\phpunit:98");
        $this->assertEquals($data, $compare, __FUNCTION__);
    }

    protected function setUp(): void
    {
        $this->debugger = new Debugger(false);
        $this->logPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    }
}
