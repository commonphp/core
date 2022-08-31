<?php

namespace Validators;

use CommonPHP\Core\Validators\MaximumLengthValidator;
use PHPUnit\Framework\TestCase;

class MaximumLengthValidatorTest extends TestCase
{
    public function testCheck()
    {
        $validator = $this->createValidator();
        $errors = [];
        $this->assertTrue($validator->check('short', '1234', $errors));
        $this->assertFalse($validator->check('long', '12345', $errors));
        $this->assertEquals($errors, ['_MESSAGE_:long']);
    }

    private function createValidator(): MaximumLengthValidator
    {
        return new MaximumLengthValidator(4, '_MESSAGE_:{name}');
    }

    public function testGetLength()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getLength(), 4);
    }

    public function testGetMessage()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getMessage('_NAME_'), '_MESSAGE_:_NAME_');
    }
}
