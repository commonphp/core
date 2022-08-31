<?php

namespace Validators;

use CommonPHP\Core\Validators\RequiredValidator;
use PHPUnit\Framework\TestCase;

class RequiredValidatorTest extends TestCase
{
    public function testGetMessage()
    {
        $validator = $this->createValidator();
        $this->assertEquals($validator->getMessage('_NAME_'), '_MESSAGE_:_NAME_');
    }

    private function createValidator(): RequiredValidator
    {
        return new RequiredValidator('_MESSAGE_:{name}');
    }

    public function testCheck()
    {
        $validator = $this->createValidator();
        $errors = [];
        $this->assertTrue($validator->check('test', 'test', $errors));
        $this->assertTrue($validator->check('padded', '   padded   ', $errors));
        $this->assertTrue($validator->check('12345', 12345, $errors));
        $this->assertFalse($validator->check('null', null, $errors));
        $this->assertFalse($validator->check('empty', '', $errors));
        $this->assertFalse($validator->check('space', '     ', $errors));
        $this->assertTrue($validator->check('false', false, $errors));
        $this->assertTrue($validator->check('true', true, $errors));
        $this->assertTrue($validator->check('zero', 0, $errors));
        $this->assertTrue($validator->check('obj', $this, $errors));
        $this->assertEquals($errors, ['_MESSAGE_:null', '_MESSAGE_:empty', '_MESSAGE_:space']);
    }
}
