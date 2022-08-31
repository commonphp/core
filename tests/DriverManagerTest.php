<?php


use CommonPHP\Core\Attributes\Driver;
use CommonPHP\Core\Contracts\DriverContract;
use CommonPHP\Core\DriverManager;
use CommonPHP\Core\Enums\DriverMode;
use CommonPHP\Core\Exceptions\DriverException;
use CommonPHP\Core\Injector;
use PHPUnit\Framework\TestCase;

#[Driver]
class DriverTest1 implements DriverContract
{
    public string $value = 'test1';
    public int $rand;

    public function __construct()
    {
        $this->rand = rand(0, 1024 * 1024);
    }
}

#[Driver]
class DriverTest2 implements DriverContract
{
    public string $value = 'test2';
    public int $rand;

    public function __construct()
    {
        $this->rand = rand(0, 1024 * 1024);
    }
}

class DriverManagerTest extends TestCase
{
    private DriverManager $driverManager;
    private Injector $injector;

    public function testSetAttributeClass()
    {
        $this->driverManager->setAttributeClass(Driver::class);
        $this->assertEquals($this->driverManager->getAttributeClass(), Driver::class);
        try {
            $this->driverManager->setAttributeClass('test');
            $this->fail('non-driver was created');
        } catch (DriverException $e) {
        }
    }

    public function testSetContractClass()
    {
        $this->driverManager->setContractClass(DriverContract::class);
        $this->assertEquals($this->driverManager->getContractClass(), DriverContract::class);
        try {
            $this->driverManager->setContractClass('test');
            $this->fail('non-driver was created');
        } catch (DriverException $e) {
        }
    }

    public function testGetLoadedClasses()
    {
        $this->driverManager->setAttributeClass(Driver::class);
        $this->driverManager->setContractClass(DriverContract::class);
        $this->assertEmpty($this->driverManager->getLoadedClasses());
        $this->driverManager->loadDriver(DriverTest1::class);
        $this->assertEquals($this->driverManager->getLoadedClasses(), [DriverTest1::class]);
    }

    public function testHasDriver()
    {
        $this->driverManager->setAttributeClass(Driver::class);
        $this->driverManager->setContractClass(DriverContract::class);
        $this->driverManager->loadDriver(DriverTest1::class);
        $this->assertTrue($this->driverManager->hasDriver(DriverTest1::class));
        $this->assertFalse($this->driverManager->hasDriver(DriverTest2::class));
    }

    public function testLoadDriver()
    {
        $this->driverManager->setAttributeClass(Driver::class);
        $this->driverManager->setContractClass(DriverContract::class);
        $this->driverManager->loadDriver(DriverTest1::class);
        $this->assertFalse($this->driverManager->hasDriver(DriverTest2::class));
    }

    public function testGetContractClass()
    {
        $this->driverManager->setContractClass(DriverContract::class);
        $this->assertEquals($this->driverManager->getContractClass(), DriverContract::class);
    }

    public function testSetMode()
    {
        $this->driverManager->setMode(DriverMode::Managed);
        $this->assertEquals($this->driverManager->getMode(), DriverMode::Managed);
    }

    public function testGetAttributeClass()
    {
        $this->driverManager->setAttributeClass(Driver::class);
        $this->assertEquals($this->driverManager->getAttributeClass(), Driver::class);
    }

    public function testGetMode()
    {
        $this->driverManager->setMode(DriverMode::Managed);
        $this->assertEquals($this->driverManager->getMode(), DriverMode::Managed);
    }

    public function testGetIterator()
    {
        $this->driverManager->setAttributeClass(Driver::class);
        $this->driverManager->setContractClass(DriverContract::class);
        $this->driverManager->loadDriver(DriverTest1::class);
        $this->driverManager->loadDriver(DriverTest2::class);
        foreach ($this->driverManager->getIterator() as $key => $driver) {
            $this->assertEquals($key, get_class($this->driverManager->getDriver($key)));
        }
    }

    public function testGetDriver()
    {
        $unmanaged = new DriverManager($this->injector);
        $unmanaged->setMode(DriverMode::Unmanaged);
        $managed = new DriverManager($this->injector);
        $managed->setMode(DriverMode::Managed);
        $service = new DriverManager($this->injector);
        $service->setMode(DriverMode::Service);
        $unmanaged->loadDriver(DriverTest1::class);
        $unmanaged->loadDriver(DriverTest2::class);
        $managed->loadDriver(DriverTest1::class);
        $managed->loadDriver(DriverTest2::class);
        $service->loadDriver(DriverTest1::class);
        $service->loadDriver(DriverTest2::class);

        /** @var DriverTest1 $unmanagedTest1 */
        $unmanagedTest1 = $unmanaged->getDriver(DriverTest1::class);
        /** @var DriverTest2 $unmanagedTest2 */
        $unmanagedTest2 = $unmanaged->getDriver(DriverTest2::class);
        /** @var DriverTest1 $unmanagedTest3 */
        $unmanagedTest3 = $unmanaged->getDriver(DriverTest1::class);
        /** @var DriverTest2 $unmanagedTest4 */
        $unmanagedTest4 = $unmanaged->getDriver(DriverTest2::class);

        /** @var DriverTest1 $managedTest1 */
        $managedTest1 = $managed->getDriver(DriverTest1::class);
        /** @var DriverTest2 $managedTest2 */
        $managedTest2 = $managed->getDriver(DriverTest2::class);
        /** @var DriverTest1 $managedTest3 */
        $managedTest3 = $managed->getDriver(DriverTest1::class);
        /** @var DriverTest2 $managedTest4 */
        $managedTest4 = $managed->getDriver(DriverTest2::class);

        /** @var DriverTest1 $serviceTest1 */
        $serviceTest1 = $service->getDriver(DriverTest1::class, [], false);
        /** @var DriverTest2 $serviceTest2 */
        $serviceTest2 = $service->getDriver(DriverTest2::class, [], false);
        /** @var DriverTest1 $serviceTest3 */
        $serviceTest3 = $service->getDriver(DriverTest1::class, [], false);
        /** @var DriverTest2 $serviceTest4 */
        $serviceTest4 = $service->getDriver(DriverTest2::class, [], false);

        $this->assertNotEquals($unmanagedTest1->value, $unmanagedTest2->value);
        $this->assertNotEquals($unmanagedTest3->value, $unmanagedTest4->value);
        $this->assertNotEquals($unmanagedTest1->rand, $unmanagedTest3->rand);
        $this->assertNotEquals($unmanagedTest2->rand, $unmanagedTest4->rand);

        $this->assertNotEquals($managedTest1->value, $managedTest2->value);
        $this->assertNotEquals($managedTest3->value, $managedTest4->value);
        $this->assertEquals($managedTest1->rand, $managedTest3->rand);
        $this->assertEquals($managedTest2->rand, $managedTest4->rand);

        $this->assertEquals($serviceTest1->value, $serviceTest2->value);
        $this->assertEquals($serviceTest3->value, $serviceTest4->value);
        $this->assertEquals($serviceTest1->rand, $serviceTest3->rand);
        $this->assertEquals($serviceTest2->rand, $serviceTest4->rand);
        /*$this->driverManager->setAttributeClass(\CommonPHP\Core\Attributes\ConfigurationDriver::class);
        $this->driverManager->setContractClass(\CommonPHP\Core\Contracts\ConfigurationDriverContract::class);
        $this->driverManager->loadDriver(\CommonPHP\Core\Drivers\JsonConfigurationDriver::class);
        $this->driverManager->getDriver(DriverManager::class);
        $this->assertTrue($this->driverManager->hasDriver(\CommonPHP\Core\Drivers\JsonConfigurationDriver::class));*/
    }

    protected function setUp(): void
    {
        $this->injector = new Injector();
        $this->driverManager = new DriverManager($this->injector);
    }
}
