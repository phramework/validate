<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

/**
 * @author Spafaridis Xenofon <nohponex@gmail.com>
 * @author Nikolopoulos Konstantinos <kosnikolopoulos@gmail.com>
 */
class UUIDv4ValidatorTest extends TestCase
{
    /**
     * @var UUIDv4Validator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new UUIDv4Validator();
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
            ['20b33445-7464-41c5-a47d-c6c41e29c77d'],
            ['2015f512-e39c-4093-b2c6-958030b57764'],
            ['db3f6f4b-df60-4b7f-bf4a-8e6bc578550c'],
            ['e1da5d7f-2d32-4cf9-b42e-9b06817c6495'],
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['100'],
            [5400000],
            ['this is an invalid string if you consider it as uuid'],
            ['knikolopoulos@vivantehealth.com'],
            ['asdasdasd-asdasdasdasd-asdasdasdasd-asdasdasd'],
            ['e1da5d7f-2d32-4cf9-b42e-9b06817c6495-9b06817c6495'],
            ['e1da5d7f-2d32-4cf9-b42e'],
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertIsString($return->value);
        $this->assertTrue($return->status);
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
            "type": "UUIDv4"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UUIDv4Validator::class, $validationObject);
    }

    public function testGetType()
    {
        $this->assertEquals('UUIDv4', $this->object->getType());
    }
}
