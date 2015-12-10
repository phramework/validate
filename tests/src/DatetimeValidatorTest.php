<?php

namespace Phramework\Validate;

class DatetimeValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DatetimeValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new DatetimeValidator();
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
            ['2000-10-12 12:00:00'],
            ['2000-10-12 12:56:00']
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
            ['2000-10-12 23']
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\DateValidator::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertSame($input, $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\DateValidator::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
     * @covers Phramework\Validate\DateValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('datetime', $this->object->getType());
    }
}
