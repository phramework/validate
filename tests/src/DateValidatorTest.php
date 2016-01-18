<?php

namespace Phramework\Validate;

class DateValidatorTest extends \PHPUnit_Framework_TestCase
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
     * @covers Phramework\Validate\DateValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new DateValidator();
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
     * @covers Phramework\Validate\DateValidator::createFromJSON
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
     * @covers Phramework\Validate\DateValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('date', $this->object->getType());
    }
}
