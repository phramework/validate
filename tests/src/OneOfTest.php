<?php

namespace Phramework\Validate;

class OneOfTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var OneOf
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new OneOf([
            new IntegerValidator(-999, -1),
            new NumberValidator(10, 30),
            new UnsignedIntegerValidator(),
            new ObjectValidator(['a' => new IntegerValidator()], ['a'], true, 0, 1),
            new ArrayValidator(2, 2, new IntegerValidator()),
            new StringValidator(2, 4),
            new StringValidator(3, 6)
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
            [0, 0], //exists only in UnsignedIntegerValidator
            [-2, -2], //exists only in IntegerValidator
            [2, 2], //exists only in UnsignedIntegerValidator
            [100, 100], //exists only in UnsignedIntegerValidator
            [13.4, 13.4], //exists only in Number
            [[1, 2], [1, 2]],
            [
                (object)['a' => 1],
                (object)['a' => 1]
            ],
            ['ab', 'ab'], //exists only in first string
            ['ababab', 'ababab'] //exists only in first string
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [10], //exists in two
            ['abc', 'abc'] //exists in both string
        ];
    }

    /**
     * @covers Phramework\Validate\OneOf::__construct
     */
    public function testConstruct()
    {
        $validator = new OneOf([
            new StringValidator(),
            new ArrayValidator(
                1,
                10,
                new StringValidator()
            )
        ]);
    }

    /**
     * @covers Phramework\Validate\OneOf::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new OneOf(['{"type": "integer"}']);
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\OneOf::validate
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);

        $this->assertEquals($expected, $return->value);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\OneOf::validate
     */
    public function testValidateFailure($input = null)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromObjectForAdditional
     */
    public function testCreateFromJSON()
    {
        $json = '{
          "oneOf": [
            {
              "type": "string",
              "minLength" : 1,
              "maxLength" : 3
            },
            {
              "type": "string",
              "minLength" : 2,
              "maxLength" : 5
            },
            {
              "type": "integer"
            }
          ]
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(OneOf::class, $validator);

        $this->assertInternalType('array', $validator->oneOf);

        //Set validator
        $this->object = $validator;

        $this->testValidateSuccess('a', 'a');
        $this->testValidateSuccess('abced', 'abced');
        $this->testValidateSuccess(10, 10);

        $this->testValidateFailure('10');
        $this->testValidateFailure('abc');

        $this->setUp();
    }

    /**
     * Validate against common enum keyword
     * @covers Phramework\Validate\OneOf::validateEnum
     */
    public function testValidateCommon()
    {
        $validator = $this->object;

        $validator->enum = [1, 2, 3, 13.4];

        $return = $validator->validate(2);
        $this->assertTrue(
            $return->status,
            'Expect true since 2 is in enum array'
        );

        $return = $validator->validate(13.4);
        $this->assertTrue(
            $return->status,
            'Expect true since 13.4 is in enum array'
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
     * @covers Phramework\Validate\OneOf::getType
     */
    public function testGetType()
    {
        $this->assertSame(null, $this->object->getType());
    }
}
