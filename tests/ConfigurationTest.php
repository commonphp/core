<?php


use CommonPHP\Core\Arrayifier;
use CommonPHP\Core\Attributes\Arrayable;
use CommonPHP\Core\Attributes\Configurable;
use CommonPHP\Core\Attributes\Populatable;
use CommonPHP\Core\Collections\ConfigurationDriverCollection;
use CommonPHP\Core\Configuration;
use CommonPHP\Core\Debugger;
use CommonPHP\Core\Filesystem;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\ServiceManager;
use PHPUnit\Framework\TestCase;

#[Configurable('test1.json')]
class ConfigurableTest
{
    #[Arrayable, Populatable]
    public string $value = 'test';
}

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Filesystem $filesystem;

    public function testRead()
    {
        $this->configuration->write('test.json', ['int' => 2353]);
        $this->assertEquals($this->configuration->read('test.json')['int'], 2353, 'test.json:int = 2353');
    }

    public function testExists()
    {
        $this->configuration->write('test.json', ['int' => 2353]);
        $this->assertTrue($this->configuration->exists('test.json'), 'test.json exists');
        $this->assertFalse($this->configuration->exists('missing.json'), 'missing.json not exists');
    }

    public function testWrite()
    {
        $time = round(microtime(true), 4);
        $filename = 'test.configuration-' . $time . '.json';
        $this->configuration->write($filename, ['test' => $time]);

        $filename = $this->filesystem->getFile('@config/' . $filename);
        $this->assertTrue(file_exists($filename), 'file_exists');
        $this->assertEquals(file_get_contents($filename), "{\n    \"test\": " . $time . "\n}", 'comparison');
    }

    public function testIsConfigurable()
    {
        $this->assertTrue($this->configuration->isConfigurable(ConfigurableTest::class));
        $this->assertFalse($this->configuration->isConfigurable(Configuration::class));
    }

    public function testLoad()
    {
        $this->configuration->write('test1.json', ['value' => 'test']);
        $this->assertEquals(get_class($this->configuration->load(ConfigurableTest::class)), ConfigurableTest::class);
    }

    public function testSave()
    {
        $this->configuration->save(new ConfigurableTest());
        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        $injector = new Injector();
        $inspector = new Inspector();
        $serviceManager = $injector->instantiate(ServiceManager::class);
        /** @var Filesystem $filesystem */
        $filesystem = $serviceManager->getService(Filesystem::class);
        $filesystem->addNamespace('config', dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config');
        $this->configuration = new Configuration(new Arrayifier($injector, $inspector), $injector, $inspector, new ConfigurationDriverCollection($injector, new Debugger(false)));
        $this->filesystem = $filesystem;
    }
}
