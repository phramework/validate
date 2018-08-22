<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class AllOfTest extends TestCase
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
            [100, 100],
            [0, 0]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [],
            [0.0000000000000000000001],
            [0.00000001],
            ['0a1'],
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

        $this->assertSame(false, $return->status);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromObjectForAdditional
     */
    public function testCreateFromJSON()
    {
        $json = '{
          "allOf": [
            {
              "type": "integer"
            },
            {
              "type": "number"
            }
          ]
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(AllOf::class, $validator);

        //Set validator
        $this->object = $validator;

        $this->assertInternalType('array', $validator->allOf);

        $this->testValidateSuccess(10, 10);
        $this->testValidateSuccess(-1, -1);

        $this->testValidateFailure(10.5);

        $this->setUp();

        return $validator;
    }

    /**
     * @covers Phramework\Validate\AllOf::toObject
     * @depends testCreateFromJSON
     */
    public function testToObject($validator)
    {
        $object = $validator->toObject();

        $this->assertObjectHasAttribute('allOf', $object);
        $this->assertInternalType('array', $object->allOf);

        $this->assertInternalType('object', $object->allOf[0]);
        $this->assertInternalType('object', $object->allOf[1]);
    }

    /**
     * @covers Phramework\Validate\AllOf::toArray
     * @depends testCreateFromJSON
     */
    public function testToArray($validator)
    {
        $object = $validator->toArray();

        $this->assertArrayHasKey('allOf', $object);
        $this->assertInternalType('array', $object['allOf']);

        $this->assertInternalType('array', $object['allOf'][0]);
        $this->assertInternalType('array', $object['allOf'][1]);
    }

    /**
     * @covers Phramework\Validate\AllOf::toJSON
     * @depends testCreateFromJSON
     */
    public function testToJSON($validator)
    {
        $json = $validator->toJSON();

        $this->assertInternalType('string', $json);

        $object = json_decode($json);

        $this->assertObjectHasAttribute('allOf', $object);
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
