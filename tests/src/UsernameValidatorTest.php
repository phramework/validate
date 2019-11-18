<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class UsernameValidatorTest extends TestCase
{

    /**
     * @var UsernameValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new UsernameValidator();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            ['nohponex'],
            ['NohponeX'],
            ['nohp_onex'],
            ['nohp_o.nex']
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['too short' =>  'ni'],
            ['too long' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'],
            ['invalid character' => 'nohponεξ'],
            ['invalid character +' => '+nohponex'],
            ['invalid character @' => '@nohponex'],
        ];
    }

    public function testConstruct()
    {
        $validator = new UsernameValidator();
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
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
            "type": "username"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UsernameValidator::class, $validationObject);
    }

    public function testGetType()
    {
        $this->assertEquals('username', $this->object->getType());
    }

    public function testSetUsernamePattern()
    {
        UsernameValidator::setUsernamePattern('/^[A-Za-z0-9_\.]{3,32}$/');
    }

    public function testGetUsernamePattern()
    {
        $pattern = '/^[A-Za-z0-9_\.]{3,6}$/';
        
        UsernameValidator::setUsernamePattern($pattern);

        $this->assertSame($pattern, UsernameValidator::getUsernamePattern());
    }
}
