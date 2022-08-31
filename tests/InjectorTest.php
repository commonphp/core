<?php /** @noinspection PhpPropertyOnlyWrittenInspection */


use CommonPHP\Core\Contracts\InjectorContract;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Inspector;
use PHPUnit\Framework\TestCase;

interface InjectorTest_TestInterface
{

}

abstract class InjectorTest_TestAbstract
{

}

class InjectorTest_TestClass extends InjectorTest_TestAbstract implements InjectorTest_TestInterface
{
    private ?Inspector $inspector;
    private Injector $injector;

    public function __construct(?Inspector $injector)
    {
        $this->inspector = $injector;
    }

    public function hasInspector()
    {
        return isset($this->inspector);
    }

    public function hasInjector()
    {
        return isset($this->injector);
    }

    function injectorTestCallMethod(InjectorTest $test, Injector $injector, Inspector $inspector, string $testValue): string
    {
        return get_class($injector) . get_class($inspector) . $testValue . get_class($test);
    }
}

class InjectorTest_TestInjector implements InjectorContract
{

    public function check(string $typeName, bool $isBuiltin): bool
    {
        return false;
    }

    public function get(string $typeName, string $signature): object
    {
        return $this;
    }
}

function injectorTestCallFunction(InjectorTest $test, Injector $injector, Inspector $inspector, string $testValue): string
{
    return get_class($injector) . get_class($inspector) . $testValue . get_class($test);
}

class InjectorTest extends TestCase
{
    public function testLoadInjector()
    {
        $injector = $this->createInjector();
        $injector->loadInjector(new InjectorTest_TestInjector());
        $this->assertTrue($injector->hasInjector(InjectorTest_TestInjector::class));
    }

    private function createInjector(): Injector
    {
        return new Injector();
    }

    public function testAddAlias()
    {
        $injector = $this->createInjector();
        $injector->addAlias(InjectorTest_TestInterface::class, InjectorTest_TestClass::class);
        $injector->addAlias(InjectorTest_TestAbstract::class, InjectorTest_TestClass::class);
        $this->assertTrue($injector->hasAlias(InjectorTest_TestInterface::class));
        $this->assertTrue($injector->hasAlias(InjectorTest_TestAbstract::class));
        $this->assertFalse($injector->hasAlias(InjectorTest_TestClass::class));
    }

    public function testCall()
    {
        $injector = $this->createInjector();
        $this->assertEquals(get_class($injector) . get_class($injector->getInspector()) . 'blah' . get_class($this), $injector->call('injectorTestCallFunction', ['test' => $this, 'testValue' => 'blah']));
    }

    public function testInject()
    {
        $injector = $this->createInjector();
        $test = new InjectorTest_TestClass(null);
        $this->assertFalse($test->hasInjector());
        $injector->inject($test);
        $this->assertTrue($test->hasInjector());
    }

    public function testInstantiate()
    {
        $injector = $this->createInjector();
        $test = new InjectorTest_TestClass(null);
        $this->assertFalse($test->hasInspector());
        /** @var InjectorTest_TestClass $test */
        $test = $injector->instantiate(InjectorTest_TestClass::class);
        $this->assertTrue($test->hasInspector());
    }

    public function testInvoke()
    {
        $injector = $this->createInjector();
        $test = new InjectorTest_TestClass(null);

        $this->assertEquals(get_class($injector) . get_class($injector->getInspector()) . 'blah' . get_class($this), $injector->invoke(InjectorTest_TestClass::class, 'injectorTestCallMethod', ['test' => $this, 'testValue' => 'blah']));
    }
}
