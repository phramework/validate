<?php

namespace Phramework\Validate;

class AnyOfTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AnyOf
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new AnyOf([
            new IntegerValidator(),
            new ArrayValidator(
                1,
                10,
                new IntegerValidator()
            )
        ]);
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
            [1, 1],
            [10, 10],
            [[10], [10]],
            [[10, 100, 32], [10, 100, 32]],
            [[10, 40], [10, 40]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [],
            ['0a1'],
            ['τρθε'],
            ['positive'],
            ['negative'],
            [['abc']],
            [['abc', 10, 32]],
            [null],
            [[]], //expectes arrays with at least one item (minItems)
            [[null]]
        ];
    }

    /**
     * @covers Phramework\Validate\AnyOf::__construct
     */
    public function testConstruct()
    {
        $validator = new AnyOf([
            new StringValidator(),
            new ArrayValidator(
                1,
                10,
                new StringValidator()
            )
        ]);
    }

    /**
     * @covers Phramework\Validate\AnyOf::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new AnyOf(['{"type": "integer"}']);
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\AnyOf::validate
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);

        if (is_array($return->value)) {
            $this->assertInternalType('array', $return->value);

            foreach ($return->value as $values) {
                $this->assertInternalType('integer', $values);
            }
        } else {
            $this->assertInternalType('integer', $return->value);
        }

        $this->assertEquals($expected, $return->value);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\AnyOf::validate
     */
    public function testValidateFailure($input = null)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
     * @covers Phramework\Validate\AnyOf::createFromJSON
     */
    public function createFromJSON()
    {

    }

    /**
     * Validate against common enum keyword
     * @covers Phramework\Validate\AnyOf::validateEnum
     */
    public function testValidateCommon()
    {
        $validator = $this->object;

        $validator->enum = [1, 2, [10, 100]];

        $return = $validator->validate(2);
        $this->assertTrue(
            $return->status,
            'Expect true since 2 is in enum array'
        );

        $return = $validator->validate([10, 100]);
        $this->assertTrue(
            $return->status,
            'Expect true since [10, 100] is in enum array'
        );

        $return = $validator->validate(10);
        $this->assertFalse(
            $return->status,
            'Expect false since 10 is not in enum array'
        );

        $return = $validator->validate([10]);
        $this->assertFalse(
            $return->status,
            'Expect false since [10] is not in enum array'
        );
    }

    /**
     * @covers Phramework\Validate\AnyOf::getType
     */
    public function testGetType()
    {
        $this->assertSame(null, $this->object->getType());
    }
}
