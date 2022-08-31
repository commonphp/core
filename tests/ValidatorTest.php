<?php /** @noinspection PhpPropertyOnlyWrittenInspection */


use CommonPHP\Core\Exceptions\ValidatorException;
use CommonPHP\Core\Inspector;
use CommonPHP\Core\Validator;
use CommonPHP\Core\Validators\EmailValidator;
use CommonPHP\Core\Validators\MaximumLengthValidator;
use CommonPHP\Core\Validators\MinimumLengthValidator;
use CommonPHP\Core\Validators\RegexValidator;
use CommonPHP\Core\Validators\RequiredValidator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    #[EmailValidator]
    private string $emailPass;
    #[EmailValidator]
    private string $emailFail;
    #[MinimumLengthValidator(4)]
    private string $minLengthPass;
    #[MinimumLengthValidator(4)]
    private string $minLengthFail;
    #[MaximumLengthValidator(4)]
    private string $maxLengthPass;
    #[MaximumLengthValidator(4)]
    private string $maxLengthFail;
    #[RegexValidator('/^[1-9][0-9]*$/ix')]
    private string $regexNumberPass;
    #[RegexValidator('/^[1-9][0-9]*$/ix')]
    private string $regexNumberFail;
    #[RegexValidator('/^(pass|not\-fail)$/ix')]
    private string $regexOptionPass;
    #[RegexValidator('/^(pass|not\-fail)$/ix')]
    private string $regexOptionFail;
    #[RequiredValidator]
    private string $requiredPass;
    #[RequiredValidator]
    private string $requiredFail;

    #[EmailValidator]
    #[MinimumLengthValidator(4)]
    #[MaximumLengthValidator(400)]
    #[RegexValidator('/^.*$/ix')]
    #[RequiredValidator]
    private string $multiPass;
    #[EmailValidator]
    #[MinimumLengthValidator(4)]
    #[MaximumLengthValidator(400)]
    #[RegexValidator('/^.*$/ix')]
    #[RequiredValidator]
    private string $multiFail;

    public function testValidate()
    {
        $this->emailPass = 'test@example.com';
        $this->emailFail = 'no-an-email';
        $this->minLengthFail = 123;
        $this->minLengthPass = 12345;
        $this->maxLengthFail = 12345;
        $this->maxLengthPass = 1234;
        $this->regexNumberFail = 'test';
        $this->regexNumberPass = 12345;
        $this->regexOptionFail = 'fail';
        $this->regexOptionPass = 'pass';
        $this->requiredPass = 'required text';
        $this->requiredFail = '';
        $this->multiPass = 'test@example.com';
        $this->multiFail = '';
        $validator = new Validator(new Inspector());
        try {
            $validator->validate($this);
        } catch (ValidatorException $e) {
            $this->assertEquals($e->getValidationErrors(), array(
                'emailFail' =>
                    array(
                        0 => 'emailFail must be an email address',
                    ),
                'minLengthFail' =>
                    array(
                        0 => 'minLengthFail must be at-least 4 characters long',
                    ),
                'maxLengthFail' =>
                    array(
                        0 => 'maxLengthFail must not exceed 4 characters',
                    ),
                'regexNumberFail' =>
                    array(
                        0 => 'regexNumberFail does not match the required pattern: /^[1-9][0-9]*$/ix',
                    ),
                'regexOptionFail' =>
                    array(
                        0 => 'regexOptionFail does not match the required pattern: /^(pass|not\\-fail)$/ix',
                    ),
                'requiredFail' =>
                    array(
                        0 => 'requiredFail is a required field',
                    ),
                'multiFail' =>
                    array(
                        0 => 'multiFail must be an email address',
                        1 => 'multiFail must be at-least 4 characters long',
                        2 => 'multiFail is a required field',
                    ),
            ), var_export($e->getValidationErrors(), true));
        }
    }
}
