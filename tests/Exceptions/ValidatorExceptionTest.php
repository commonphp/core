<?php

namespace Exceptions;

use CommonPHP\Core\Exceptions\ValidatorException;
use PHPUnit\Framework\TestCase;

class ValidatorExceptionTest extends TestCase
{
    public function testAddValidationError()
    {
        $exception = $this->createValidatorException();
        $exception->addValidationError('error3');
        $this->assertSame($exception->countValidationErrors(), 3);
        $this->assertEquals($exception->getValidationErrors(), ['error1', 'error2', 'error3']);
    }

    private function createValidatorException(): ValidatorException
    {
        return new ValidatorException(['error1', 'error2']);
    }

    public function testCountValidationErrors()
    {
        $exception = $this->createValidatorException();
        $this->assertSame($exception->countValidationErrors(), 2);
    }

    public function testHasValidationErrors()
    {
        $exception = $this->createValidatorException();
        $this->assertTrue($exception->hasValidationErrors());
    }

    public function testGetValidationErrors()
    {
        $exception = $this->createValidatorException();
        $this->assertEquals($exception->getValidationErrors(), ['error1', 'error2']);
    }
}
