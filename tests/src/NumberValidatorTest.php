<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-2019-10-05 at 20:04:03.
 */
class NumberValidatorTest extends TestCase
{
    /**
     * @var NumberValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new NumberValidator(-1000, 1000, true);
    }


    public function validateSuccessProvider()
    {
        //input, expected (float)
        return [
            ['100', 100.0],
            [124, 124.0],
            [0, 0.0],
            [-10, -10.0],
            [-99, -99.0],
            [3.5, 3.5],
            ['13.5', 13.5],
            ['-23.6', -23.6]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['a'],
            ['abc'],
            ['-0x'],
            ['abc'],
            ['+xyz'],
            ['++30'],
            [-1000], //should fail because of exclusiveMinimum
            [-10000000],
            [10000000],
            ['-1000000000']
        ];
    }

    /**
     * @expectedException \DomainException
     */
    public function testConstructFailure6()
    {
        $validator = new NumberValidator(
            2,
            1
        );
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testCreateFromJSON($input, $expected)
    {
        $json = '{
            "type": "number",
            "minimum" : -1000,
            "maximum" : 1000,
            "title": "my number",
            "default": 10,
            "x-extra": "not existing"
        }';

        /** @var NumberValidator $validatorObject */
        $validatorObject = NumberValidator::createFromJSON($json);

        $this->assertSame(
            'my number',
            $validatorObject->title,
            'Title must be passed'
        );

        $this->assertFalse(
            $validatorObject->exclusiveMaximum
        );

        $this->assertFalse(
            $validatorObject->exclusiveMinimum
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
    private function validateSuccess(NumberValidator $object, $input, $expected)
    {
        $return = $object->validate($input);

        $this->assertTrue($return->status);
        $this->assertIsFloat($return->value);
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
     * @dataProvider validateSuccessProvider
     */
    public function testValidateNumberSuccess($input, $expected)
    {
        $this->validateSuccess($this->object, $input, $expected);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateNumberFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     */
    public function testValidateFailureMultipleOf()
    {
        $validator = new NumberValidator(null, null, null, null, 2);
        $return = $validator->validate(5);

        $this->assertEquals('multipleOf', $return->exception->getFailure());
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommon()
    {
        $validator = (new NumberValidator(0, 10));

        $validator->enum = [1, 3.5, 5];

        $return = $validator->validate(3.5);
        $this->assertTrue(
            $return->status,
            'Expect true since "3.5" is in enum array'
        );

        $return = $validator->validate(2);
        $this->assertFalse(
            $return->status,
            'Expect false since "2" is not in enum array'
        );
    }

    /**
     */
    public function testGetType()
    {
        $this->assertEquals('number', $this->object->getType());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldFailWhenMultipleOfIsNotPositiveNumber()
    {
        new NumberValidator(
            null,
            null,
            null,
            null,
            -1
        );
    }

    /**
     * @expectedException \DomainException
     */
    public function testAllowExclusiveMaximumOnlyIfMaximumIsSet()
    {
        new NumberValidator(
            null,
            null,
            null,
            true
        );
    }

    /**
     * @expectedException \DomainException
     */
    public function testAllowExclusiveMinimumOnlyIfMinimumIsSet()
    {
        new NumberValidator(
            null,
            null,
            true,
            false
        );
    }
}
