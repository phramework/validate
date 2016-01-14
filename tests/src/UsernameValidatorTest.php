<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class UsernameValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UsernameValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new UsernameValidator();
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
            ['nohponex'],
            ['NohponeX'],
            ['nohp_onex'],
            ['nohp_o.nex'],
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['too short' =>  'ni'],
            ['too long'            => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'],
            ['invalid character'   => 'nohponεξ'],
            ['invalid character +' => '+nohponex'],
            ['invalid character @' => '@nohponex'],
        ];
    }

    /**
     * @covers Phramework\Validate\UsernameValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new UsernameValidator();
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\UsernameValidator::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\UsernameValidator::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\UsernameValidator::createFromJSON
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "username"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UsernameValidator::class, $validationObject);
    }

    /**
     * @covers Phramework\Validate\UsernameValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('username', $this->object->getType());
    }

    /**
     * @covers Phramework\Validate\UsernameValidator::setUsernamePattern
     */
    public function testSetUsernamePattern()
    {
        UsernameValidator::setUsernamePattern('/^[A-Za-z0-9_\.]{3,32}$/');
    }

    /**
     * @covers Phramework\Validate\UsernameValidator::getUsernamePattern
     */
    public function testGetUsernamePattern()
    {
        $pattern = '/^[A-Za-z0-9_\.]{3,6}$/';

        UsernameValidator::setUsernamePattern($pattern);

        $this->assertSame($pattern, UsernameValidator::getUsernamePattern());
    }
}
