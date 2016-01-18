<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class EmailValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EmailValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new EmailValidator(10, 30);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            ['nohponex@gmail.com'],
            ['nohponex_under@gmail.com'],
            ['nohponex@mail.co.uk']
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['string' =>  '100'],
            ['less than 10 characters' => 'nx@ma.il'],
            ['dotless' => 'nohponex@gmailcom'],
            ['longer' => 'nohponex_long_long_long_long_long@gmail.com'],
            ['without@' => 'dasdjs#sdads.fd'],
            ['number' => 124],
        ];
    }

    /**
     * @covers Phramework\Validate\EmailValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new EmailValidator();
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\EmailValidator::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\EmailValidator::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\EmailValidator::createFromJSON
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "email"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(EmailValidator::class, $validationObject);
    }

    /**
     * @covers Phramework\Validate\EmailValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('email', $this->object->getType());
    }
}
