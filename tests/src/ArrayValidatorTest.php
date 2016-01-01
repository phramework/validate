<?php

namespace Phramework\Validate;

class ArrayValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ArrayValidator
     */
    protected $object;

    /**
     * Sets up the fixture
     */
    protected function setUp()
    {
        $this->object = new ArrayValidator(1, 3);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\ArrayValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new ArrayValidator(
            1,
            3,
            new IntegerValidator(),
            true,
            false
        );
    }

    public function validateSuccessProvider()
    {
        //input
        return [
            [[2, '3']],
            [['2', '3']],
            [[1, 2, 3]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['0 items' => []],
            ['>3 items' => [1,2,3,4,5,6]]
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\ArrayValidator::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('array', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\ArrayValidator::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
        $this->assertInstanceOf(
            \Phramework\Exceptions\IncorrectParametersException::class,
            $return->errorObject
        );
    }

    /**
     * @covers Phramework\Validate\ArrayValidator::validate
     */
    public function testValidateItems()
    {
        $validator = new ArrayValidator(
            1,
            2,
            new EnumValidator(['one', 'two', 'three', 'four'], true),
            true,
            false
        );

        $this->assertInstanceOf(BaseValidator::class, $validator->items);
        $this->assertInstanceOf(EnumValidator::class, $validator->items);

        $return = $validator->validate(['one', 'two']);

        $this->assertTrue($return->status);

        $return = $validator->validate(['four']);
        $this->assertTrue($return->status);

        $return = $validator->validate(['one', 'two', 'four']);
        $this->assertFalse($return->status, 'Since we have maxItems "2"');

        $return = $validator->validate(['one', 'not a valid value']);
        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\ArrayValidator::createFromJSON
     */
    public function testCreateFromJSON()
    {
        $json = '{
          "type": "array",
          "minItems": 1,
          "maxItems": 2,
          "title": "demo array",
          "description": "Pick 1 or 2 options",
          "additionalItems": false,
          "items": {
            "type": "enum",
            "enum": [
              "one",
              "two",
              "three",
              "four"
            ],
            "validateType": true
          },
          "uniqueItems": true
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(ArrayValidator::class, $validator);

        $this->assertSame(
            1,
            $validator->minItems
        );
        $this->assertSame(
            2,
            $validator->maxItems
        );

        $this->assertInstanceOf(BaseValidator::class, $validator->items);
        $this->assertInstanceOf(EnumValidator::class, $validator->items);

        $return = $validator->validate(['one', 'four']);
        $this->assertTrue($return->status);

        $return = $validator->validate(['one', 'two', 'three']);
        $this->assertFalse($return->status);

        $return = $validator->validate(['one', 'bad value']);
        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\ArrayValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('array', $this->object->getType());
    }
}
