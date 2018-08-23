<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class URLValidatorTest extends TestCase
{

    /**
     * @var URL
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new URLValidator(3, 100);
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
            ['https://nohponex.gr'],
            ['http://www.thmmy.gr/dir/file.php?param=ok&second=false#ok'],
            ['http://127.0.0.1/app']
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['100'],
            [540],
            ['nx@ma.il'],
            ['nohponex@gmailcom'],
            ['http::://nohponex.gr'],
            ['nohponex.gr'],
            ['nohponex'],
            ['//nohponex.gr']
        ];
    }

    public function testConstruct()
    {
        $validator = new URLValidator();
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
            "type": "url",
            "minLength" : 10,
            "maxLength" : 100
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(URLValidator::class, $validationObject);

        $this->assertSame(
            10,
            $validationObject->minLength
        );

        $this->assertSame(
            100,
            $validationObject->maxLength
        );
    }

    public function testGetType()
    {
        $this->assertEquals('url', $this->object->getType());
    }
}
