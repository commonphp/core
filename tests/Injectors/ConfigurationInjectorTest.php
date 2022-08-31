<?php

namespace Injectors;

use CommonPHP\Core\Arrayifier;
use CommonPHP\Core\Attributes\Arrayable;
use CommonPHP\Core\Attributes\Configurable;
use CommonPHP\Core\Attributes\Populatable;
use CommonPHP\Core\Collections\ConfigurationDriverCollection;
use CommonPHP\Core\Configuration;
use CommonPHP\Core\Debugger;
use CommonPHP\Core\Filesystem;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Injectors\ConfigurableInjector;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\ServiceManager;
use PHPUnit\Framework\TestCase;

#[Configurable('test1.json')]
class ConfigurableTest
{
    #[Arrayable, Populatable]
    public string $value = 'test';
}

class ConfigurationInjectorTest extends TestCase
{

    private ConfigurableInjector $target;
    private Configuration $configuration;

    public function testCheck()
    {
        $this->assertTrue($this->target->check(ConfigurableTest::class, false), ConfigurableTest::class);
        $this->assertFalse($this->target->check(ConfigurableInjector::class, false), ConfigurableInjector::class);
        $this->assertFalse($this->target->check('string', true), 'string');
    }

    public function testGet()
    {
        $this->assertTrue($this->target->check(ConfigurableTest::class, false), ConfigurableTest::class);
        $this->assertEquals(get_class($this->target->get(ConfigurableTest::class, 'TEST')), ConfigurableTest::class);
    }

    protected function setUp(): void
    {
        $injector = new Injector();
        $inspector = new Inspector();
        $serviceManager = $injector->instantiate(ServiceManager::class);
        /** @var Filesystem $filesystem */
        $filesystem = $serviceManager->getService(Filesystem::class);
        $filesystem->addNamespace('config', dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config');
        $this->configuration = new Configuration(new Arrayifier($injector, $inspector), $injector, $inspector, new ConfigurationDriverCollection($injector, new Debugger(false)));

        $this->target = new ConfigurableInjector($this->configuration);
    }
}
