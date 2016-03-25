<?php

namespace Phramework\Validate;

/**
 * @coversDefaultClass Phramework\Validate\ObjectValidator
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
        $properties = (object) [
            'str' => new \Phramework\Validate\StringValidator(2, 4),
            'ok' => new \Phramework\Validate\BooleanValidator(),
        ];

        $this->object = new ObjectValidator(
            $properties,
            ['ok'],
            true,
            2,
            2
        );
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
            [(object)['ok' => false, 'okk' => 'xyz' ]],
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [1], //not an array or object
            [['ok']], //`ok` is not an object key
            [['abc']],
            [(object)['str' => 'my strxxxxxxxxxxx', 'ok' => false]],
            [(object)['str' => 'my str', 'okk' => false]],
            [(object)(['okk' => 'hello'])], //because missing ok
            [['ok'=> 'omg', 'okk' => '2']], //because of ok is not boolean
            [(object)['ok' => 'true', 'str' => 'my str', 'okk' => '123']], //maxProperties
            [(object)['ok' => 'true']] //minProperties
        ];
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $validator = new ObjectValidator();
    }

    /**
     * @covers ::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new ObjectValidator(
            (object) [],
            [],
            null,
            -1
        );
    }

    /**
     * @covers ::__construct
     * @expectedException Exception
     */
    public function testConstructFailure1()
    {
        $validator = new ObjectValidator(
            (object) [],
            [],
            null,
            2,
            1
        );
    }

    /**
     * @covers ::__construct
     * @expectedException Exception
     */
    public function testConstructFailure2()
    {
        $validator = new ObjectValidator(
            (object) [],
            [],
            [],
            1,
            2
        );
    }

    /**
     * @todo MUST be remove when BaseValidator are supported for "additionalProperties"
     * @covers ::__construct
     * @expectedException Exception
     */
    public function testConstructFailure3()
    {
        $validator = new ObjectValidator(
            (object) [
                'obj' => new IntegerValidator()
            ],
            ['obj'],
            new IntegerValidator()
        );
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers ::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertTrue($return->status);
        $this->assertInternalType('object', $return->value);
    }

    public function testValidateDependencies()
    {
        $validator = new ObjectValidator(
            (object) [
                'name'            => new StringValidator(),
                'credit_card'     => new StringValidator(),
                'billing_address' => new StringValidator()
            ],
            ['name'],
            false,
            0,
            null,
            (object) [
                'credit_card' => ['billing_address']
            ]
        );

        $validator->parse((object) [
            'name' => 'Jane Doe'
        ]);

        $validator->parse((object) [
            'name'            => 'Jane Doe',
            'billing_address' => '127.0.0.1'
        ]);

        $validator->parse((object) [
            'name'            => 'Jane Doe',
            'credit_card'     => '5555-5555-5555-5555',
            'billing_address' => '127.0.0.1'
        ]);


        $this->expectException(\Phramework\Exceptions\MissingParametersException::class);
        $validator->parse((object) [
            'name'            => 'Jane Doe',
            'credit_card'     => '5555-5555-5555-5555', //billing_address is a dependency
        ]);
    }

    /**
     * @covers ::validate
     */
    public function testValidateRecursiveSuccess()
    {
        $validationObject = new ObjectValidator(
            (object) [
                'order' => (new UnsignedIntegerValidator())
                    ->setDefault(0),
                'request' => (new ObjectValidator(
                    (object) [
                        'url' => new StringValidator(3, 256),
                        'method' => (new StringValidator(3, 10))
                            ->setDefault('GET'),
                        'response' => new ObjectValidator(
                            (object) [
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
            (object)[
                'order' => 5,
                'request' => (object)[
                    'url' => 'account/',
                    'response' => (object)[
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
     * @covers ::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers ::validate
     */
    public function testValidateFailureMissing()
    {
        $validationObject = new ObjectValidator(
            (object) [
                'key' => new StringValidator()
            ],
            ['key'],
            true
        );

        $return = $validationObject->validate((object)['ok' => 'true']);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            \Phramework\Exceptions\MissingParametersException::class,
            $return->exception
        );

        //$parameters = $return->exception->getParameters();

        //$this->assertContains('key', $parameters);

        $validationObject = new ObjectValidator(
            (object) [
                'key' => new StringValidator(),
                'obj' => new ObjectValidator(
                    (object) [
                        'o' => new StringValidator()
                    ],
                    ['o'],
                    true
                )
            ],
            ['obj']
        );

        $return = $validationObject->validate((object)[
            'ok' => 'true',
            'obj' => (object)[
                'ok' => false
            ]
        ]);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            \Phramework\Exceptions\MissingParametersException::class,
            $return->exception
        );
    }
    /**
     * @covers ::validate
     */
    public function testValidateFailureAdditionalProperties()
    {
        $validationObject = new ObjectValidator(
            (object) [
                'key' => new StringValidator()
            ],
            ['key'],
            false
        );

        $return = $validationObject->validate((object)[
            'key' => '1',
            'additional' => 'true'
        ]);

        $this->assertFalse($return->status);
        
        $this->assertInstanceOf(
            'Phramework\\Exceptions\\IncorrectParameterException',
            $return->exception
        );

        $this->assertEquals(
            'additionalProperties',
            $return->exception->getFailure()
        );
    }

    /**
      * @covers ::addProperties
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
      * @covers ::addProperties
      * @expectedException Exception
     */
    public function testAddPropertiesFailure()
    {
        $properties = 104;
        $this->object->addProperties($properties); //Not an array
    }

    /**
     * @covers ::addProperties
     * @expectedException Exception
     */
    public function testAddPropertiesFailure2()
    {
        $this->object->addProperties([]);
    }

    /**
      * @covers ::addProperty
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
      * @covers ::addProperty
      * @expectedException Exception
     */
    public function testAddPropertyFailure()
    {
        $property = new ObjectValidator();
        $this->object->addProperty('new', $property);

        $this->object->addProperty('new', $property); //With same key
    }

    /**
     * @covers ::getType
     */
    public function testGetType()
    {
        $this->assertEquals('object', $this->object->getType());
    }

    /**
     * @covers ::parse
     */
    public function testParseSuccess()
    {
        $input = (object) [
            'weight' => '5',
            'obj' => (object)[
                'valid' => 'true',
                'number' => 10.2,
            ]
        ];

        $validationObject = new ObjectValidator(
            (object) [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    (object) [ //properties
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
     * @covers ::parse
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
            (object) [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    (object) [ //properties
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
     * @covers ::parse
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
            (object) [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    (object) [ //properties
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
    }

    /**
     * @covers ::setValidateCallback
     */
    public function testSetValidateCallback()
    {
        $value = 10;

        $validator = (new ObjectValidator())
            ->setValidateCallback(function ($validateResult, $validator) use ($value) {
                $validateResult->value->obj = $value;

                return $validateResult;
            });

        $this->assertInstanceOf(ObjectValidator::class, $validator);

        $parsed = $validator->parse((object) [
            'obj' => 5
        ]);

        $this->assertSame($value, $parsed->obj);

        $validator = (new ObjectValidator())
            ->setValidateCallback(function ($validateResult, $validator) use ($value) {
                $validateResult->value = null;

                return $validateResult;
            });

        $parsed = $validator->parse((object) [
            'obj' => 5
        ]);

        $this->assertNull($parsed);
    }

    /**
     * @covers ::validate
     */
    public function testValidateSetDefault() {
        $validator = (new ObjectValidator(
            (object) [
                'name'            => new StringValidator(),
                'address'         => (new ObjectValidator((object) [
                    'street' => new StringValidator(),
                    'number' => new UnsignedIntegerValidator(),
                    'floor'  => (new IntegerValidator())->setDefault(1)
                ]))->setDefault((object) [
                    'floor' => 0
                ])
            ]
        ))->setDefault((object) [
            'address' => (object) [
                'floor' => -1
            ]
        ]);

        //Use root default
        $parsed = $validator->parse((object) []);

        $this->assertInternalType('object', $parsed->address);
        $this->assertSame(-1, $parsed->address->floor);

        //Use default
        $parsed = $validator->parse((object) [
            'name' => 'Jane Doe'
        ]);

        $this->assertSame(0, $parsed->address->floor);

        //Use passed
        $parsed = $validator->parse((object) [
            'name' => 'Jane Doe',
            'address' => (object) [
                'street' => 'My Street'
            ]
        ]);

        $this->assertSame(1, $parsed->address->floor);
    }

    /**
     * @covers ::validate
     */
    public function testValidateSetDefaultNull() {
        $validator = (new ObjectValidator(
            (object) [
                'name' => new StringValidator()
            ]
        ))->setDefault(null);

        $parsed = $validator->parse((object) []);

        $this->assertNull($parsed);
    }
}
