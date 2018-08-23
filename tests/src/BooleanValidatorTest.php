<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class BooleanValidatorTest extends TestCase
{

    /**
     * @var BooleanValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BooleanValidator();
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
            [1, true],
            ['1', true],
            [true, true],
            ['true', true],
            ['TRUE', true],
            ['yes', true],
            ['on', true],
            [0, false],
            ['0', false],
            [false, false],
            ['false', false],
            ['FALSE', false],
            ['no', false],
            ['off', false]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['100'],
            ['01'],
            [10],
            [-1],
            [124],
            ['τρθε'],
            ['positive'],
            ['negative']
        ];
    }

    public function testConstruct()
    {
        $validator = new BooleanValidator();
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('boolean', $return->value);
        $this->assertEquals($expected, $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommon()
    {
        $validator = (new BooleanValidator());

        $validator->enum = [true];

        $return = $validator->validate(true);
        $this->assertTrue(
            $return->status,
            'Expect true since true is in enum array'
        );

        $return = $validator->validate(false);
        $this->assertFalse(
            $return->status,
            'Expect false since false is not in enum array'
        );
    }

    public function testGetType()
    {
        $this->assertEquals('boolean', $this->object->getType());
    }
}
