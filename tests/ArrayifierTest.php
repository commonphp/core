<?php


use CommonPHP\Core\Arrayifier;
use CommonPHP\Core\Attributes\Arrayable;
use CommonPHP\Core\Attributes\Populatable;
use CommonPHP\Core\Injector;
use CommonPHP\Core\Inspector;
use PHPUnit\Framework\TestCase;

class ArrayifierTest extends TestCase
{
    private Injector $injector;
    private Inspector $inspector;
    private Arrayifier $arrayifier;
    private $class;

    public function testPopulate()
    {
        $obj = $this->createTestObject();
        $test1 = $obj->encodeAll();
        $this->arrayifier->populate($obj, [
            'int_value' => 321,
            'bool_value' => false,
            'string_value' => 'unpopulated',
            'arrayable_only' => 2,
            'populatable_only' => 2,
            'object_value' => 'Exception',
            'ignored_value' => 'ignored'
        ]);
        $test2 = $obj->encodeAll();
        $this->assertTrue($test1 !== $test2, $test1 . "\n" . $test2);
    }

    private function createTestObject(): object
    {
        return new class($this->injector, 123, true, 'unpopulated', 0, 0, new Exception('with message'), 'empty') {
            #[Arrayable, Populatable]
            private int $int_value = 5;

            #[Arrayable, Populatable]
            private bool $bool_value = true;

            #[Arrayable, Populatable]
            private string $string_value = 'string';

            #[Arrayable]
            private int $arrayable_only = 1;

            #[Populatable]
            private int $populatable_only = 2;

            #[Arrayable('getClass'), Populatable('setClass')]
            private object $object_value;

            private string $ignored_value;

            private Injector $injector;

            public function __construct(Injector $injector, int $int_value, bool $bool_value, string $string_value, int $arrayable_only, int $populatable_only, object $object_value, string $ignored_value)
            {
                $this->int_value = $int_value;
                $this->bool_value = $bool_value;
                $this->string_value = $string_value;
                $this->arrayable_only = $arrayable_only;
                $this->populatable_only = $populatable_only;
                $this->object_value = $object_value;
                $this->ignored_value = $ignored_value;
                $this->injector = $injector;
            }

            public function encodeAll(): string
            {
                return json_encode([
                    'int_value' => $this->int_value,
                    'bool_value' => $this->bool_value,
                    'string_value' => $this->string_value,
                    'arrayable_only' => $this->arrayable_only,
                    'object_value' => $this->getClass(),
                ]);
            }

            public function encodeArrayable(): string
            {
                return json_encode([
                    'int_value' => $this->int_value,
                    'bool_value' => $this->bool_value,
                    'string_value' => $this->string_value,
                    'arrayable_only' => $this->arrayable_only,
                    'populatable_only' => $this->populatable_only,
                    'object_value' => $this->getClass(),
                    'ignored_value' => $this->ignored_value
                ]);
            }

            public function getIntValue(): int
            {
                return $this->int_value;
            }

            public function getBoolValue(): bool
            {
                return $this->bool_value;
            }

            public function getStringValue(): string
            {
                return $this->string_value;
            }

            public function getArrayableOnly(): int
            {
                return $this->arrayable_only;
            }

            public function getPopulatableOnly(): int
            {
                return $this->populatable_only;
            }

            public function getObjectValue(): object
            {
                return $this->object_value;
            }

            public function getIgnoredValue(): string
            {
                return $this->ignored_value;
            }

            function getClass(): string
            {
                return get_class($this->object_value);
            }

            function setClass(string $value): void
            {
                $this->object_value = $this->injector->instantiate($value);
            }
        };
    }

    public function testArrayify()
    {
        $obj = $this->createTestObject();
        $test1 = $obj->encodeArrayable();
        $test2 = json_encode($this->arrayifier->arrayify($obj));
        $this->assertTrue($test1 !== $test2, $test1 . "\n" . $test2);
    }

    protected function setUp(): void
    {
        $this->injector = new Injector();
        $this->inspector = new Inspector();
        $this->arrayifier = new Arrayifier($this->injector, $this->inspector);
    }
}
