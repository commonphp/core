<?php

namespace Validators;

use CommonPHP\Core\Validators\MinimumLengthValidator;
use PHPUnit\Framework\TestCase;

class MinimumLengthValidatorTest extends TestCase
{
    public function testGetMessage()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getMessage('_NAME_'), '_MESSAGE_:_NAME_');
    }

    private function createValidator(): MinimumLengthValidator
    {
        return new MinimumLengthValidator(4, '_MESSAGE_:{name}');
    }

    public function testGetLength()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getLength(), 4);
    }

    public function testCheck()
    {
        $validator = $this->createValidator();
        $errors = [];
        $this->assertFalse($validator->check('short', '123', $errors));
        $this->assertTrue($validator->check('long', '1234', $errors));
        $this->assertEquals($errors, ['_MESSAGE_:short']);
    }
}
