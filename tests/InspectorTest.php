<?php


use CommonPHP\Core\Exceptions\InspectorException;
use CommonPHP\Core\Inspector;
use PHPUnit\Framework\TestCase;

#[Attribute]
class InspectorTest_TestAttribute
{

}

#[InspectorTest_TestAttribute]
class InspectorTest extends TestCase
{
    public function testValidateInstance()
    {
        $inspector = new Inspector();
        try {
            $inspector->validateInstance($this, self::class);
            $this->assertTrue(true);
        } catch (InspectorException $e) {
            $this->fail('Not the same instance');
        }
        try {
            $inspector->validateInstance($this, Exception::class);
            $this->fail('Not the same instance');
        } catch (InspectorException $e) {
            $this->assertTrue(true);
        }
    }

    public function testHasSingleReflectedAttribute()
    {
        $inspector = new Inspector();
        $this->assertTrue($inspector->hasSingleReflectedAttribute(new ReflectionClass($this), InspectorTest_TestAttribute::class));
        $this->assertFalse($inspector->hasSingleReflectedAttribute(new ReflectionMethod($this, 'testHasSingleReflectedAttribute'), InspectorTest_TestAttribute::class));
    }
}
