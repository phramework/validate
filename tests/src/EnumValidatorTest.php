<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class EnumValidatorTest extends TestCase
{

    /**
     * @var EnumValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new EnumValidator(['1', '2', [1, 2, 3], 'ok', 5], true);
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
            ['1', '1'],
            ['2', '2'],
            ['ok', 'ok'],
            [5, 5],
            [[1,2,3], [1, 2, 3]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [1],
            [2],
            ['5'],
            [4],
            ['7'],
            ['string']
        ];
    }

    public function testConstruct()
    {
        $validator = new EnumValidator(
            ['1', '2', 'ok', 5],
            true
        );
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);
        $this->assertSame($expected, $return->value);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    public function testCreateFromJSON()
    {
        $json = '{
            "type": "enum",
            "enum": [1, 2, 3]
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(EnumValidator::class, $validationObject);
    }

    public function testCreateFromJSONAndValidate()
    {
        $json = '{
            "type": "enum",
            "enum": [1, 2, 3],
            "validateType": false
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(EnumValidator::class, $validationObject);

        $validationObject->parse('1');
        $validationObject->parse(1);
    }

    /**
     * @expectedException \Phramework\Exceptions\IncorrectParametersException
     */
    public function testCreateFromJSONAndValidateType()
    {
        $json = '{
            "type": "enum",
            "enum": [1, 2, 3],
            "validateType": true
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $validationObject->parse('1');
    }

    public function testGetType()
    {
        $this->assertEquals('enum', $this->object->getType());
    }
}
