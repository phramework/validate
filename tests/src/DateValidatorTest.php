<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class DateValidatorTest extends TestCase
{
    /**
     * @var DateValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new DateValidator();
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
            ['2000-10-12'],
            ['2000-01-02']
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
            ['2014-01-33'],
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
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);
    }

    /**
     * @expectedException \Exception
     */
    public function testFormatMinimumFailure()
    {
        $validator = new DateValidator(
            '2000-10-12'
        );

        $validator->parse('2000-10-11');
    }

    public function testParse()
    {
        $validator = new DateValidator(
            '2000-10-10',
            '2000-10-12'
        );

        $this->assertSame(
            '2000-10-11',
            $validator->parse('2000-10-11')
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testFormatMaximumFailure()
    {
        $validator = new DateValidator(
            null,
            '2000-10-10'
        );

        $validator->parse('2000-10-12');
    }

    /**
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "date"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(DateValidator::class, $validationObject);
    }

    /**
     */
    public function testGetType()
    {
        $this->assertEquals('date', $this->object->getType());
    }
}
