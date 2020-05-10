<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class IntegerValidatorTest extends TestCase
{

    /**
     * @var IntegerValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new IntegerValidator(-1000, 1000, true, true);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    public function validateSuccessProvider(): array
    {
        //input, expected
        return [
            ['100', 100],
            [124, 124],
            [0, 0],
            [-10, -10],
            [-99, -99]
        ];
    }

    public function validateFailureProvider(): array
    {
        //input
        return [
            ['-0x', 'type'],
            ['abc', 'type'],
            ['+xyz', 'type'],
            ['++30', 'type'],
            [-1000, 'minimum'], //should fail because of exclusiveMinimum
            [1000, 'maximum'], //should fail because of exclusiveMaximum
            [-10000000, 'minimum'],
            [10000000, 'maximum'],
            ['-1000000000', 'minimum'],
            [1.4, 'multipleOf'],
            [-13.5, 'multipleOf'],
        ];
    }

    public function testConstruct()
    {
        $validator = new IntegerValidator(
            0,
            1
        );

        $this->assertInstanceOf('\Phramework\Validate\NumberValidator', $validator);
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure()
    {
        $validator = new IntegerValidator(
            'a'
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure2()
    {
        $validator = new IntegerValidator(
            1,
            'a'
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure3()
    {
        $validator = new IntegerValidator(
            1,
            2,
            null,
            null,
            'a'
        );
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testCreateFromJSON($input, $expected)
    {
        $json = '{
            "type": "integer",
            "minimum" : -1000,
            "maximum" : 1000,
            "title": "my int",
            "default": 10,
            "x-extra": "not existing"
        }';

        $validatorObject = IntegerValidator::createFromJSON($json);

        $this->assertSame(
            'my int',
            $validatorObject->title,
            'Title must be passed'
        );

        $this->assertSame(
            10,
            $validatorObject->default,
            'Default must be passed'
        );

        $this->assertObjectNotHasAttribute(
            'x-extra',
            $validatorObject,
            'Attribute must not exists'
        );

        //use helper function to validate $input against this validator
        $this->validateSuccess($validatorObject, $input, $expected);
    }

    /**
     * Helper method
     */
    private function validateSuccess(IntegerValidator $object, $input, $expected)
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
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input, $failure)
    {
        $return = $this->object->validate($input);

        $parameters = $return->errorObject->getParameters();

        $this->assertFalse($return->status);
        $this->assertEquals($failure, $parameters[0]['failure']);
    }

    public function testValidateFailureMultipleOf()
    {
        $validator = new IntegerValidator(null, null, null, null, 2);
        $return = $validator->validate(5);

        $this->assertFalse($return->status);

        $parameters = $return->errorObject->getParameters();

        $this->assertEquals('multipleOf', $parameters[0]['failure']);
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommon()
    {
        $validator = (new IntegerValidator(0, 10));

        $validator->enum = [1, 3, 5];

        $return = $validator->validate(1);
        $this->assertTrue(
            $return->status,
            'Expect true since "1" is in enum array'
        );

        $return = $validator->validate(2);
        $this->assertFalse(
            $return->status,
            'Expect false since "2" is not in enum array'
        );
    }

    public function testGetType()
    {
        $this->assertEquals('integer', $this->object->getType());
    }
}
