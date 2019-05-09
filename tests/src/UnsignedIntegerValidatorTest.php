<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class UnsignedIntegerValidatorTest extends TestCase
{

    /**
     * @var UnsignedIntegerValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new UnsignedIntegerValidator(10, 1000, true);
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
            ['100', 100],
            [124, 124]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['-0x'],
            ['abc'],
            ['+xyz']
            [-1000],
            ['-4'],
            [4], //because of min,
            [1.4],
            [-13.5]
        ];
    }

    /**
     */
    public function testConstruct()
    {
        $validator = new UnsignedIntegerValidator(
            0,
            1
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure()
    {
        $validator = new UnsignedIntegerValidator(
            -1
        );
    }

    /**
     * Helper method
     */
    private function validateSuccess(UnsignedIntegerValidator $object, $input, $expected)
    {
        $return = $object->validate($input);

        $this->assertTrue($return->status);
        $this->assertInternalType('integer', $return->value);
        $this->assertSame($expected, $return->value);
    }

    /**
          * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $this->validateSuccess($this->object, $input, $expected);
    }

    /**
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "unsignedinteger"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UnsignedIntegerValidator::class, $validationObject);
    }

    /**
     */
    public function testCreateFromJSONAlias()
    {
        $json = '{
            "type": "uint"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UnsignedIntegerValidator::class, $validationObject);
    }

    /**
     */
    public function testGetType()
    {
        $this->assertEquals('unsignedinteger', $this->object->getType());
    }

    public function testSetValidateCallback()
    {
        $value = 5;

        $validator = (new UnsignedIntegerValidator())
            ->setValidateCallback(function ($validateResult, $validator) {
                $validateResult->value = 3; //change the value inside callback

                return $validateResult;
            });

        $this->assertInstanceOf(UnsignedIntegerValidator::class, $validator);

        $parsed = $validator->parse($value);

        $this->assertSame(3, $parsed);
    }
}
