<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class ObjectValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ObjectValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $properties = [
            'str' => new \Phramework\Validate\StringValidator(2, 4),
            'ok' => new \Phramework\Validate\BooleanValidator(),
        ];

        $this->object = new ObjectValidator($properties, ['ok'], true, 2, 2);
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
        //input
        return [
            [(object)['ok' => true, 'str2' => 'my str']],
            [(object)['ok' => 'true', 'okk' => '123']],
            [(object)['ok' => false, 'okk' => 'xyz' ]]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [1], //not an array or object
            [['ok']], //`ok` is not an object key
            [['abc']],
            [(object)['str' => 'my str', 'okk' => false]],
            [(object)(['okk' => 'hello'])], //because missing ok
            [['ok'=> 'omg', 'okk' => '2']], //because of ok is not boolean
            [(object)['ok' => 'true', 'str' => 'my str', 'okk' => '123']], //maxProperties
            [(object)['ok' => 'true']] //minProperties
        ];
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new ObjectValidator();
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new ObjectValidator(
            [],
            [],
            null,
            -1
        );
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure1()
    {
        $validator = new ObjectValidator(
            [],
            [],
            null,
            2,
            1
        );
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure2()
    {
        $validator = new ObjectValidator(
            [],
            [],
            [],
            1,
            2
        );
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\ObjectValidator::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);
        $this->assertInternalType('object', $return->value);

    }

    /**
     * @covers Phramework\Validate\ObjectValidator::validate
     */
    public function testValidateRecursiveSuccess()
    {
        $validationObject = new ObjectValidator(
            [
                'order' => (new UnsignedIntegerValidator())
                    ->setDefault(0),
                'request' => (new ObjectValidator(
                    [
                        'url' => new StringValidator(3, 256),
                        'method' => (new StringValidator(3, 10))
                            ->setDefault('GET'),
                        'response' => new ObjectValidator(
                            [
                                'statusCode' => (new UnsignedIntegerValidator(100, 999))
                                    ->setDefault(200),
                                'default' => (new UnsignedIntegerValidator(100, 999))
                                    ->setDefault(200),
                                'ruleObjects' => (new ArrayValidator())
                            ],
                            ['statusCode', 'ruleObjects']
                        )
                    ],
                    ['url', 'response']
                ))
            ],
            ['request']
        );

        //prevent bug when contents of attributes are changed
        $validationObject->toArray();

        $this->assertInstanceOf(BaseValidator::class, $validationObject);
        $this->assertInstanceOf(ObjectValidator::class, $validationObject);
        $this->assertInstanceOf(
            UnsignedIntegerValidator::class,
            $validationObject->properties->order
        );
        $this->assertInstanceOf(
            ObjectValidator::class,
            $validationObject->properties->request
        );
        $this->assertInstanceOf(
            StringValidator::class,
            $validationObject->properties->request->properties->method
        );
        $this->assertSame(
            'GET',
            $validationObject->properties->request->properties->method->default
        );
        $this->assertInstanceOf(
            ObjectValidator::class,
            $validationObject->properties->request->properties->response
        );
        $this->assertInstanceOf(
            ArrayValidator::class,
            $validationObject->properties->request->properties->response->properties->ruleObjects
        );
        $this->assertInstanceOf(
            UnsignedIntegerValidator::class,
            $validationObject->properties->request->properties->response->properties->statusCode
        );
        $this->assertSame(
            200,
            $validationObject->properties->request->properties->response->properties->statusCode->default
        );

        $parsed = $validationObject->parse(
            [
                'order' => 5,
                'request' => [
                    'url' => 'account/',
                    'response' => [
                        'statusCode' => 400,
                        'ruleObjects' => ['abc', 'cda']
                    ]
                ]
            ]
        );

        $this->assertSame(
            5,
            $parsed->order
        );

        $this->assertSame(
            400,
            $parsed->request->response->statusCode
        );

        $this->assertSame(
            200,
            $parsed->request->response->default
        );

        $this->assertInternalType(
            'array',
            $parsed->request->response->ruleObjects
        );
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\ObjectValidator::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::validate
     */
    public function testValidateFailureMissing()
    {
        $validationObject = new ObjectValidator(
            [
                'key' => new StringValidator()
            ],
            ['key'],
            false
        );

        $return = $validationObject->validate(['ok' => 'true']);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            'Phramework\\Exceptions\\MissingParametersException',
            $return->errorObject
        );

        $parameters = $return->errorObject->getParameters();

        $this->assertContains('key', $parameters);

        $validationObject = new ObjectValidator(
            [
                'key' => new StringValidator(),
                'obj' => new ObjectValidator(
                    [
                        'o' => new StringValidator()
                    ],
                    ['o'],
                    true
                )
            ],
            ['obj']
        );

        $return = $validationObject->validate([
            'ok' => 'true',
            'obj' => [
                'ok' => false
            ]
        ]);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            \Phramework\Exceptions\MissingParametersException::class,
            $return->errorObject
        );
    }
    /**
     * @covers Phramework\Validate\ObjectValidator::validate
     */
    public function testValidateFailureAdditionalProperties()
    {
        $validationObject = new ObjectValidator(
            [
                'key' => new StringValidator()
            ],
            ['key'],
            false
        );

        $return = $validationObject->validate(['key' => '1', 'additiona' => 'true']);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            'Phramework\\Exceptions\\IncorrectParametersException',
            $return->errorObject
        );

        $parameters = $return->errorObject->getParameters();

        $this->assertEquals('additionalProperties', $parameters[0]['failure']);
    }

    /**
      * @covers Phramework\Validate\ObjectValidator::addProperties
     */
    public function testAddPropertiesSuccess()
    {
        $originalPropertiesCount = count(get_object_vars(
            $this->object->properties
        ));
        $properties = ['new_property' => new ObjectValidator()];
        $this->object->addProperties($properties);

        //Test if number of properties is increased by count of added properties
        $this->assertEquals(
            $originalPropertiesCount + count($properties),
            count(get_object_vars($this->object->properties))
        );
    }

    /**
      * @covers Phramework\Validate\ObjectValidator::addProperties
      * @expectedException Exception
     */
    public function testAddPropertiesFailure()
    {
        $properties = 104;
        $this->object->addProperties($properties); //Not an array
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::addProperties
     * @expectedException Exception
     */
    public function testAddPropertiesFailure2()
    {
        $this->object->addProperties([]);
    }

    /**
      * @covers Phramework\Validate\ObjectValidator::addProperty
     */
    public function testAddPropertySuccess()
    {
        $key = 'my_key';
        $property = new ObjectValidator();
        $this->object->addProperty($key, $property);

        $this->assertTrue(
            array_key_exists($key, $this->object->properties)
        );
    }

    /**
      * @covers Phramework\Validate\ObjectValidator::addProperty
      * @expectedException Exception
     */
    public function testAddPropertyFailure()
    {
        $property = new ObjectValidator();
        $this->object->addProperty('new', $property);

        $this->object->addProperty('new', $property); //With same key
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('object', $this->object->getType());
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::parse
     */
    public function testParseSuccess()
    {
        $input = [
            'weight' => '5',
            'obj' => [
                'valid' => 'true',
                'number' => 10.2,
            ]
        ];

        $validationObject = new ObjectValidator(
            [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    [ //properties
                        'valid' => new BooleanValidator(),
                        'number' => new NumberValidator(0, 100),
                        'not_required' => (new NumberValidator(0, 100))->setDefault(5.5),
                    ],
                    ['valid'] //required
                )
            ],
            ['weight'] //required
        );

        $record = $validationObject->parse($input);

        $this->assertInternalType('object', $record);
        $this->assertInternalType('object', $record->obj);
        $this->assertInternalType('float', $record->obj->not_required);
        $this->assertEquals(5, $record->weight);
        $this->assertTrue($record->obj->valid);
        $this->assertEquals(5.5, $record->obj->not_required);
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::parse
     * @expectedException Exception
     * @todo \Phramework\Exceptions\MissingParametersException
     */
    public function testParseFailure()
    {
        $input = [
            'weight' => '5',
            'obj' => [
                //'valid' => 'true',
                'number' => 10.2,
            ]
        ];

        $validationObject = new ObjectValidator(
            [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    [ //properties
                        'valid' => new BooleanValidator(),
                        'number' => new NumberValidator(0, 100),
                        'not_required' => (new NumberValidator(0, 100))
                            ->setDefault(5.5),
                    ],
                    ['valid'] //required
                )
            ],
            ['weight'] //required
        );

        $record = $validationObject->parse($input);
    }

    /**
     * @covers Phramework\Validate\ObjectValidator::parse
     * @expectedException Exception
     * @todo \Phramework\Exceptions\IncorrectParametersException
     */
    public function testParseFailure2()
    {
        $input = [
            'weight' => '555', //out of range
            'obj' => [
                'valid' => 'ΝΟΤ_VALID',
                'number' => 10.2
            ]
        ];

        $validationObject = new ObjectValidator(
            [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    [ //properties
                        'valid' => new BooleanValidator(),
                        'number' => new NumberValidator(0, 100),
                        'not_required' => (new NumberValidator(0, 100))
                            ->setDefault(5),
                    ],
                    ['valid'] //required
                )
            ],
            ['weight'] //required
        );

        $record = $validationObject->parse($input);

        $this->assertInternalType('object', $return->value);
    }
}
