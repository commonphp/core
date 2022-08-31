<?php

namespace Validators;

use CommonPHP\Core\Validators\EmailValidator;
use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase
{
    public function testCheck()
    {
        $validator = $this->createValidator();
        $errors = [];
        $this->assertTrue($validator->check('_TRUE_', 'test@example.com', $errors));
        $this->assertFalse($validator->check('_FALSE_', 'not-an-email', $errors));
        $this->assertEquals($errors, ['_MESSAGE_:_FALSE_']);
    }

    private function createValidator(): EmailValidator
    {
        return new EmailValidator('_MESSAGE_:{name}');
    }

    public function testGetMessage()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getMessage('_NAME_'), '_MESSAGE_:_NAME_');
    }
}
