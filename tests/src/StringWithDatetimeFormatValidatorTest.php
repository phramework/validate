<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class StringWithDatetimeFormatValidatorTest extends TestCase
{

    /**
     * @var String
     */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new StringValidator(
            20,
            35,
            null,
            false,
            'date-time'
        );
    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            ['2019-11-14T14:30:26+02:00', '2019-11-14T14:30:26+02:00'],
            ['2019-11-14T14:30:60+02:00', '2019-11-14T14:30:60+02:00'],
            ['2019-11-14T14:30:60+00:00', '2019-11-14T14:30:60+00:00'],
            ['2019-11-14T14:30:45-05:00', '2019-11-14T14:30:45-05:00'],
            ['2019-11-14T00:00:00-05:00', '2019-11-14T00:00:00-05:00'],
            ['2019-11-14T14:30:26Z', '2019-11-14T14:30:26Z'],
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['2019-11-14T14:30:26Z+02:00', 'date-time'],
            ['2019-11-14T', 'minLength'],
            ['2019-11-14 14:30:26+02:00', 'date-time'],
            ['2019-11-14T14:30:26+02:00random', 'date-time'],
            ['2019-11-14T14:30:26.123543333654+02:00', 'maxLength'],
            ['2019-02-30T01:01:01Z', 'date-time'],
            ['2019-13-30T01:01:01Z', 'date-time'],
            ['asdfasdf', 'minLength'],
            ['a708465e-8fec-4508-b159-46d545de3b', 'date-time'],
            ['2019-11-14T00:00:00-99:00', 'date-time'],
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
        $this->assertEquals($expected, $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input, $failure)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);

        $expectedError =
            [
                'type' => 'string',
                'failure' => $failure
            ];

        $this->assertAttributeContains($expectedError, 'parameters', $return->errorObject);
    }

    public function testGetType()
    {
        $this->assertEquals('string', $this->object->getType());
    }
}
