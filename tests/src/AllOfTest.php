<?php

namespace Phramework\Validate;

class AllOfTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AllOf
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new AllOf([
            new IntegerValidator(),
            new UnsignedIntegerValidator(),
            new NumberValidator()
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
            [100, 100]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [],
            ['01'],
            ['τρθε'],
            ['positive'],
            ['negative'],
            [['abc']],
            [['abc', 10, 32]],
            [0.1],
            [-10],
            [-1]
        ];
    }

    /**
     * @covers Phramework\Validate\AllOf::__construct
     */
    public function testConstruct()
    {
        $validator = new AllOf([
            new StringValidator(),
            new ArrayValidator(
                1,
                10,
                new StringValidator()
            )
        ]);
    }

    /**
     * @covers Phramework\Validate\AllOf::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new AllOf(['{"type": "integer"}']);
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\AllOf::validate
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
     * @covers Phramework\Validate\AllOf::validate
     */
    public function testValidateFailure($input = null)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
     * @covers Phramework\Validate\AllOf::createFromJSON
     */
    public function createFromJSON()
    {

    }

    /**
     * Validate against common enum keyword
     * @covers Phramework\Validate\AllOf::validateEnum
     */
    public function testValidateCommon()
    {
        $validator = $this->object;

        $validator->enum = [1, 2, 3];

        $return = $validator->validate(2);
        $this->assertTrue(
            $return->status,
            'Expect true since 2 is in enum array'
        );

        $return = $validator->validate(1.1);
        $this->assertFalse(
            $return->status,
            'Expect false since 1.1 is not in enum array'
        );

        $return = $validator->validate([10]);
        $this->assertFalse(
            $return->status,
            'Expect false since [10] is not in enum array'
        );
    }

    /**
     * @covers Phramework\Validate\AllOf::getType
     */
    public function testGetType()
    {
        $this->assertSame(null, $this->object->getType());
    }
}
