<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class DatetimeValidatorTest extends TestCase
{

    /**
     * @var DatetimeValidator
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new DatetimeValidator();
    }

    public function validateSuccessProvider()
    {
        return [
            ['2000-10-12 12:00:00'],
            ['2000-01-12 02:56:00'],
            ['2000-10-12 00:56:00'],
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['10-10-2014'],
            ['20'],
            ['10-13-2014'],
            ['2014-13-10'],
            ['2014-13-33'],
            ['2000-10-12 12:60:00'],
            ['2000-10-12 12:56:60'],
            ['2000-10-12 25:56:00'],
            ['2000-10-12 23:56'],
            ['2000-10-12'],
            ['2000-10-12 23'],
            ['2000-10-12 0:34:00'],
            ['2000-1-12 00:34:00'],
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertSame($input, $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @expectedException \Exception
     */
    public function testFormatMinimumFailure()
    {
        $validator = new DatetimeValidator(
            '2000-10-12 12:00:00'
        );

        $validator->parse('2000-10-11 12:00:00');
    }

    public function testFormatMinimumMaximumSuccess()
    {
        $validator = new DatetimeValidator(
            '2000-10-12 12:00:00',
            '2000-10-12 12:01:00'
        );

        $this->assertSame(
            '2000-10-12 12:00:01',
           $validator->parse('2000-10-12 12:00:01')
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testFormatMaximumFailure()
    {
        $validator = new DatetimeValidator(
            null,
            '2000-10-12 12:00:00'
        );

        $validator->parse('2000-11-12 12:00:00');
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
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "date-time"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(DatetimeValidator::class, $validationObject);
    }

    /**
     */
    public function testGetType()
    {
        $this->assertEquals('date-time', $this->object->getType());
    }
}
