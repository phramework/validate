<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class ArrayValidatorTest extends TestCase
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

    /**
     */
    public function testConstruct2()
    {
        new ArrayValidator(
            1,
            null
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure1()
    {
        $validator = new ArrayValidator(
            1,
            3,
            []
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure3()
    {
        $validator = new ArrayValidator(
            3,
            1
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure4()
    {
        $validator = new ArrayValidator(
            1,
            3,
            new \stdClass()
        );
    }

    public function validateSuccessProvider()
    {
        //input
        return [
            [[2, '3']],
            [['2', '3']],
            [[1, 2, 3]],
            [[1,2]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [1],
            ['0 items' => []],
            ['>3 items' => [1,2,3,4,5,6]]
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('array', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
        $this->assertInstanceOf(
            \Phramework\Exceptions\IncorrectParameterException::class,
            $return->exception
        );
    }

    /**
     */
    public function testValidateUnique()
    {
        $validator = new ArrayValidator(
            1,
            2,
            new EnumValidator(['one', 'two', 'three', 'four'], true),
            true
        );

        $return = $validator->validate(['one', 'one']);

        $this->assertFalse($return->status);

        $return = $validator->validate('one');

        $this->assertFalse($return->status);
    }

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

    public function testValidateUniqueObject()
    {
        $validator = new ArrayValidator(
            1,
            2,
            new ObjectValidator(
                (object) [
                    'value' => new EnumValidator(['1', '2'], true),
                ],
                ['value'],
                false
            ),
            true
        );

        $return = $validator->validate([
            (object) ['value' => '1'],
            (object) ['value' => '1'],
            (object) ['value' => '2'],
        ]);

        $this->assertFalse($return->status);
    }

    /**
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
     */
    public function testGetType()
    {
        $this->assertEquals('array', $this->object->getType());
    }

    /**
     */
    public function testEquals()
    {
        $this->assertTrue(
            ArrayValidator::equals(
                [0, 1],
                [0, 1]
            )
        );

        $this->assertTrue(
            ArrayValidator::equals(
                [0, 1],
                [1, 0]
            )
        );


        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                [0, 4]
            )
        );

        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                [0, 1, 3]
            )
        );

        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                []
            )
        );

        $this->assertFalse(
            ArrayValidator::equals(
                [0, 1],
                [0]
            )
        );
    }

    /**
     */
    public function testSetValidateCallback()
    {
        $value = [1, 2];

        $validator = (new ArrayValidator())
            ->setValidateCallback(
            /**
             * @param Result        $validateResult
             * @param BaseValidator $validator
             * @return Result
             */
                function ($validateResult, $validator) use ($value) {
                    $validateResult->value = $value;

                    return $validateResult;
                });

        $this->assertInstanceOf(ArrayValidator::class, $validator);

        $parsed = $validator->parse(['a', 'b', 'c']);

        $this->assertInternalType('array', $parsed);
        $this->assertEquals($value, $parsed);
    }
}
