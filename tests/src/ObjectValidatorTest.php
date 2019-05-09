<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;
use Phramework\Exceptions\Exception;
use Phramework\Exceptions\IncorrectParameterException;
use Phramework\Exceptions\Source\Pointer;

/**
 */
class ObjectValidatorTest extends TestCase
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
            'str' => new StringValidator(2, 4),
            'ok'  => new BooleanValidator(),
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
            [(object) ['ok' => true, 'str2' => 'my str']],
            [(object) ['ok' => 'true', 'okk' => '123']],
            [(object) ['ok' => false, 'okk' => 'xyz' ]],
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            [1], //not an array or object
            [['ok']], //`ok` is not an object key
            [['abc']],
            [(object) ['str' => 'my strxxxxxxxxxxx', 'ok' => false]],
            [(object) ['str' => 'my str', 'okk' => false]],
            [(object) (['okk' => 'hello'])], //because missing ok
            [['ok'=> 'omg', 'okk' => '2']], //because of ok is not boolean
            [(object) ['ok' => 'true', 'str' => 'my str', 'okk' => '123']], //maxProperties
            [(object) ['ok' => 'true']] //minProperties
        ];
    }

    /**
     */
    public function testConstruct()
    {
        $validator = new ObjectValidator();
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure()
    {
        $validator = new ObjectValidator(
            (object) [],
            [],
            true,
            -1
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure1()
    {
        $validator = new ObjectValidator(
            (object) [],
            [],
            true,
            2,
            1
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure2()
    {
        $validator = new ObjectValidator(
            (object) [],
            [],
            [new IntegerValidator()], //does not accept arrays
            1,
            2
        );
    }

    /**
     * @dataProvider validateSuccessProvider
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
            (object) [
                'order' => 5,
                'request' => (object) [
                    'url' => 'account/',
                    'response' => (object) [
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
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
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

        $return = $validationObject->validate((object) ['ok' => 'true']);

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

        $return = $validationObject->validate((object) [
            'ok' => 'true',
            'obj' => (object) [
                'ok' => false
            ]
        ]);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            \Phramework\Exceptions\IncorrectParametersException::class,
            $return->exception
        );

        $this->markTestIncomplete('Test internal exceptions');
    }
    /**
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

        $return = $validationObject->validate((object) [
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
       * @expectedException \Exception
     */
    public function testAddPropertiesFailure()
    {
        $properties = 104;
        $this->object->addProperties($properties); //Not an array
    }

    /**
     * @expectedException \Exception
     */
    public function testAddPropertiesFailure2()
    {
        $this->object->addProperties([]);
    }

    /**
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
     */
    public function testGetType()
    {
        $this->assertEquals('object', $this->object->getType());
    }

    /**
     */
    public function testParseSuccess()
    {
        $input = (object) [
            'weight' => '5',
            'obj' => (object) [
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
     * @expectedException \Exception
     * @todo \Phramework\Exceptions\MissingParametersException
     */
    public function testParseFailure()
    {
        $input = (object) [
            'weight' => '5',
            'obj' => [
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
     * @expectedException \Exception
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
     */
    public function testValidateSetDefault()
    {
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
     */
    public function testValidateSetDefaultNull()
    {
        $validator = (new ObjectValidator(
            (object) [
                'name' => new StringValidator()
            ]
        ))->setDefault(null);

        $parsed = $validator->parse((object) []);

        $this->assertNull($parsed);
    }

    /**
     */
    public function testXVisibility()
    {
        $validator = new ObjectValidator(
            (object) [
                'field1' => new EnumValidator(
                    ['yes', 'no']
                ),
                'field2' => new StringValidator(),
            ],
            ['field1', 'field2'],
            false,
            0,
            null,
            null,
            (object) [
                'field2' => [
                    'member',
                    'field1',
                    ['yes']
                ]
            ]
        );

        $result = $validator->parse((object) [
            'field1' => 'no'
        ]);

        //Expect exception
        $this->expectException(
            IncorrectParameterException::class
        );
            
        $result = $validator->parse((object) [
            'field1' => 'no',
            'field2' => 'abcd'
        ]);

        $this->markTestIncomplete();
    }

    /**
     */
    public function testXVisibilityOR()
    {
        $validator = new ObjectValidator(
            (object) [
                'field1' => new EnumValidator(
                    ['yes', 'no', 'dk']
                ),
                'field2' => new StringValidator(),
            ],
            ['field1', 'field2'],
            false,
            0,
            null,
            null,
            (object) [
                'field2' => [
                    'or',
                    [
                        'member',
                        'field1',
                        ['yes']
                    ],
                    [
                        'member',
                        'field1',
                        ['dk']
                    ]
                ]
            ]
        );

        $result = $validator->parse((object) [
            'field1' => 'yes',
            'field2' => 'asdf'
        ]);

        //Expect exception
        $this->expectException(
            IncorrectParameterException::class
        );

        $result = $validator->parse((object) [
            'field1' => 'no',
            'field2' => 'abcd'
        ]);

        $this->markTestIncomplete();
    }

    /**
     */
    public function testValidateAdditionalProperties()
    {
        $validator = (new ObjectValidator(
            null,
            [],
            new IntegerValidator()
        ))->setSource(new Pointer(''));

        $validator->parse((object) [
            'i' => 10
        ]);

        $this->expectException(IncorrectParameterException::class);

        $validator->parse((object) [
            'i' => 'abcd'
        ]);
    }

    /**
     */
    public function testAdditionalPropertiesFromJSON()
    {
        $schema = '{
          "type": "object",
          "properties": {},
          "additionalProperties": {
            "type": "number"
          }
        }';

        $validator = BaseValidator::createFromJSON($schema);

        $validator->parse((object) [
            'i' => 10
        ]);

        $this->expectException(IncorrectParameterException::class);

        $validator->parse((object) [
            'i' => 'abcd'
        ]);
    }

    /**
     */
    public function testPatternPropertiesFromJSON()
    {
        $schema = '{
          "type": "object",
          "properties": {
            "builtin": { "type": "number" }
          },
          "patternProperties": {
            "^S_": { "type": "string" },
            "^I_": { "type": "integer" }
          },
          "additionalProperties": { "type": "string" }
        }';

        $validator = BaseValidator::createFromJSON($schema);

        $validator->parse((object) [
            'builtin' => 10
        ]);

        $validator->parse((object) [
            'keyword' => "a string"
        ]);


        $validator->parse((object) [
            'S_tring' => "a string"
        ]);


        $validator->parse((object) [
            'I_nteger' => 10
        ]);

        $this->expectException(IncorrectParameterException::class);

        $validator->parse((object) [
            'keyword' => 10
        ]);
    }
}
