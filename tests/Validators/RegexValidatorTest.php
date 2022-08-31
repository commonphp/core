<?php

namespace Validators;

use CommonPHP\Core\Validators\RegexValidator;
use PHPUnit\Framework\TestCase;

class RegexValidatorTest extends TestCase
{
    public function testGetMessage()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getMessage('_NAME_'), '_MESSAGE_:_NAME_');
    }

    private function createValidator(): RegexValidator
    {
        return new RegexValidator('/^[1-9][0-9]*$/ix', '_MESSAGE_:{name}');
    }

    public function testMatches()
    {
        $validator = $this->createValidator();
        $this->assertTrue($validator->matches('123434'));
        $this->assertFalse($validator->matches('test'));
        $this->assertFalse($validator->matches('12.34'));
        $this->assertTrue($validator->matches(true));
        $this->assertFalse($validator->matches(false));
    }

    public function testCheck()
    {
        $validator = $this->createValidator();
        $errors = [];
        $this->assertTrue($validator->check('int', '123434', $errors));
        $this->assertFalse($validator->check('string', 'test', $errors));
        $this->assertFalse($validator->check('float', '12.34', $errors));
        $this->assertTrue($validator->check('true', true, $errors));
        $this->assertFalse($validator->check('false', false, $errors));
        $this->assertEquals($errors, ['_MESSAGE_:string', '_MESSAGE_:float', '_MESSAGE_:false']);
    }
}
