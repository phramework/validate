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

use Phramework\Exceptions\IncorrectParameterException;
use Phramework\Exceptions\IncorrectParametersException;
use Phramework\Exceptions\MissingParametersException;
use Phramework\Exceptions\Source\ISource;
use Phramework\Exceptions\Source\Pointer;
use Phramework\Validate\Result\Result;

/**
 * BaseValidator, every validator **MUST** extend this class
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 * @property string title
 * @property string description
 * @property mixed default
 * @property string format
 * @property array enum
 * @property BaseValidator not
 */
abstract class BaseValidator
{
    /**
     * @var ISource
     */
    protected $source = null;

    /**
     * @var callable|null
     */
    protected $validateCallback = null;

    /**
     * Validator's type
     * Must be overwritten, default is 'string'
     * @var string|null
     */
    protected static $type = null;

    /**
     * This static method will instantiate a new object as validation model
     * to parse the input value
     * @param mixed $value Input value to validate
     */
    public static function parseStatic($value)
    {
        $validationObject = new static();

        return $validationObject->parse($value);
    }

    /**
    * Validate value
    * @see Result for ValidateResult object
    * @param  mixed $value Input value to validate
    * @return Result
     */
    abstract public function validate($value);

    /**
     * Common helper method to validate against all common keywords
     * @uses validateEnum
     * @param  mixed  $value Value to validate
     * @param  Result $validateResult Current ValidateResult status
     * @return Result
     */
    protected function validateCommon($value, $validateResult)
    {
        //While current status of validation is true,
        //keep validating against common keywords

        //validate against enum using validateEnum
        if ($validateResult->status === true && $this->enum) {
            $validateResult = $this->validateEnum($value, $validateResult->value);

            if ($validateResult->status !== true) {
                //failed to validate against enum
                return $validateResult;
            }
        }

        //validate against not
        if ($validateResult->status === true && $this->not) {
            $validateResult = $this->validateNot($value, $validateResult->value);

            if ($validateResult->status !== true) {
                //failed to validate against not
                return $validateResult;
            }
        }

        return $this->runValidateCallback($validateResult);
    }

    /**
     * @todo May cause issues when parent validator calls
     * this method and then child type casts the returned value (see number and integer validator)
     * @param Result $validateResult
     * @return Result
     */
    protected function runValidateCallback($validateResult)
    {
        //Use returned value from validate callback is set
        if ($validateResult->status === true && $this->validateCallback !== null) {
            return call_user_func(
                $this->validateCallback,
                $validateResult,
                $this
            );
        }

        return $validateResult;
    }

    /**
     * Common helper method to validate against "enum" keyword
     * @see 5.5.1. enum http://json-schema.org/latest/json-schema-validation.html#anchor75
     * @param  mixed $value Value to validate
     * @param  mixed $value Parsed value from previous validators
     * @return Result
     * @todo provide support for objects and arrays
     */
    protected function validateEnum($value, $parsedValue)
    {
        $return = new Result($parsedValue, false);

        //Check if $this->enum is set and it's not null since its optional
        if ($this->enum !== null) {
            if (is_object($value)) {
                throw new \Exception('Objects are not supported');
            }

            //Search current $value in enum
            foreach ($this->enum as $v) {
                if (is_object($v)) {
                    throw new \Exception('Objects are not supported');
                }

                if ($value == $v) {
                    if ($this->validateType && ($valueType = gettype($value)) !== ($vType = gettype($v))) {
                        continue;
                    }

                    //Success value is found

                    //Overwrite $return's value (get correct object type)
                    $return->value = $v;

                    //Set status to true
                    $return->status = true;

                    return $return;
                } elseif (is_array($value)
                    && is_array($v)
                    && ArrayValidator::equals($value, $v)
                ) {
                    //Type is same (arrays)
                    //Success value is found

                    //Overwrite $return's value (get correct object type)
                    $return->value = $v;

                    //Set status to true
                    $return->status = true;

                    return $return;
                }
            }

            $return->status = false;
            //Error
            $return->exception = new IncorrectParameterException(
                'enum',
                null,
                $this->source
            );
        }

        return $return;
    }

    /**
     * Common helper method to validate against "not" keyword
     * @param  mixed $value       Value to validate
     * @param  mixed $parsedValue Parsed value from previous validators
     * @return Result
     * @throws \Exception
     */
    protected function validateNot($value, $parsedValue)
    {
        $return = new Result($parsedValue, true);

        //Check if $this->not is set and it's not null since its optional
        if ($this->not && $this->not !== null) {
            if (!is_subclass_of(
                $this->not,
                BaseValidator::class,
                true
            )) {
                throw new \Exception(sprintf(
                    'Property "not" MUST extend "%s"',
                    BaseValidator::class
                ));
            }

            $validateNot = $this->not->validate($value);

            if ($validateNot->status === true) {
                //Error
                $return->status = false;
                $return->exception = new IncorrectParameterException(
                    'not',
                    null,
                    $this->source
                );

                return $return;
            }
        }

        return $return;
    }

    /**
     * Get validator's type
     * @return string|null
     */
    public static function getType()
    {
        return static::$type;
    }

    /**
     * Validator's attributes
     * Can be overwritten
     * @var string[]
     */
    protected static $typeAttributes = [
    ];

    /**
     * Common validator attributes
     * @var string[]
     */
    protected static $commonAttributes = [
        'title',
        'description',
        'default',
        'format',
        'enum',
        'validateType', //non standard attribute, can be used in combination with enum
        'not'
    ];

    public $default;

    /**
     * Get validator's attributes
     * @return string[]
     */
    public static function getTypeAttributes()
    {
        return static::$typeAttributes;
    }

    /**
     * Objects current attributes and values
     * @var array
     */
    protected $attributes = [];

    /**
     * BaseValidator constructor.
     */
    protected function __construct()
    {
        //Append common attributes
        foreach (static::$commonAttributes as $attribute) {
            $this->attributes[$attribute] = null;
        }

        //Append type attributes
        foreach (static::$typeAttributes as $attribute) {
            $this->attributes[$attribute] = null;
        }
    }

    /**
     * Get attribute's value
     * @param  string $key Attribute's key
     * @return mixed
     * @throws \Exception If key not found
     */
    public function __get($key)
    {
        if ($key === 'type') {
            return $this->getType();
        }

        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception(sprintf(
                'Unknown key "%s" to get',
                $key
            ));
        }

        return $this->attributes[$key];
    }

    /**
     * Set attribute's value
     * @param string $key   Attribute's key
     * @param mixed $value  Attribute's value
     * @throws \Exception If key not found
     * @return $this
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->attributes)) {
            throw new \Exception(sprintf(
                'Unknown key "%s" to set',
                $key
            ));
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array|null $enum
     * @return $this
     */
    public function setEnum($enum)
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @param BaseValidator|null $not
     * @return $this
     * @throws \Exception
     */
    public function setNot($not)
    {
        if ($not !== null && !is_subclass_of(
            $not,
            BaseValidator::class,
            true
        )) {
            throw new \Exception(sprintf(
                'Property "not" MUST extend "%s"',
                BaseValidator::class
            ));
        }

        $this->not = $not;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Set validation callback, this
     * @param callable $callback Callback method may have two arguments, first is
     * @return $this
     * @throws \Exception When callback is not callable
     * @example
     * ```php
     * //Will increase the parsed value by one
     * $validator = (new IntegerValidator())
     *     ->setValidateCallback(function ($validateResult, $validator) {
     *         $validateResult->value = $validateResult->value + 1;
     *
     *         return $validateResult;
     *     });
     *
     * $parsed = $validator->parse(5); //Expect 6
     * ```
     * @since 0.6.0
     */
    public function setValidateCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Callback is not callable');
        }

        $this->validateCallback = $callback;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param ISource|null $source
     * @return $this
     */
    public function setSource(ISource $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * This method use this validator to parse data from $value argument
     * and return a clean object
     * @param  mixed $value Input value to validate
     * @throws \Phramework\Exceptions\MissingParametersException
     * @throws \Phramework\Exceptions\IncorrectParametersException
     * @return mixed
     */
    public function parse($value)
    {
        $validateResult = $this->validate($value);

        if (!$validateResult->status) {
            throw $validateResult->exception;
        }

        $typeCastedValue = $validateResult->value;

        return $typeCastedValue;
    }

    /**
     * Validator classes registry
     * @var string[]
     */
    private static $validatorRegistry = [
        'string'            => StringValidator::class,
        'url'               => URLValidator::class,
        'number'            => NumberValidator::class,
        'float'             => NumberValidator::class, //alias
        'integer'           => IntegerValidator::class,
        'int'               => IntegerValidator::class, //alias
        'unsignedinteger'   => UnsignedIntegerValidator::class,
        'uinteger'          => UnsignedIntegerValidator::class, //alias
        'uint'              => UnsignedIntegerValidator::class, //alias
        'boolean'           => BooleanValidator::class,
        'bool'              => BooleanValidator::class, //alias
        'date'              => DateValidator::class,
        'date-time'         => DatetimeValidator::class,
        'datetime'          => DatetimeValidator::class, //alias
        'enum'              => EnumValidator::class,
        'array'             => ArrayValidator::class,
        'object'            => ObjectValidator::class,
        'email'             => EmailValidator::class,
        'username'          => UsernameValidator::class,
    ];

    /**
     * Register a custom validator for a type
     * @param  string $type      Type's name, `x-` SHOULD be used for custom types
     * @param  string $className Validator's full classn ame.
     * All validators MUST extend `BaseValidator` class
     * @example
     * ```php
     * BaseValidator::registerValidator('x-address', 'My\APP\AddressValidator');
     * ```
     * @throws \Exception
     */
    public static function registerValidator($type, $className)
    {
        if (!is_string($type)) {
            throw new \Exception('"type" MUST be string');
        }

        if (!is_string($className)) {
            throw new \Exception('"className" MUST be string');
        }

        if (!is_subclass_of(
            $className,
            BaseValidator::class,
            true
        )) {
            throw new \Exception(sprintf(
                '"className" MUST extend "%s"',
                BaseValidator::class
            ));
        }

        self::$validatorRegistry[$type] = $className;
    }

    /**
     * Helper method.
     * Used to create anyOf, allOf and oneOf validators from objects
     * @param  object $object Validation object
     * @return AnyOf|AllOf|OneOf|null
     */
    protected static function createFromObjectForAdditional($object)
    {
        $indexProperty = null;
        $class = null;

        if (property_exists($object, 'anyOf')) {
            $indexProperty = 'anyOf';
            $class = AnyOf::class;
        } elseif (property_exists($object, 'allOf')) {
            $indexProperty = 'allOf';
            $class = AllOf::class;
        } elseif (property_exists($object, 'oneOf')) {
            $indexProperty = 'oneOf';
            $class = OneOf::class;
        } else {
            return null;
        }

        //Parse index property's object as validator
        foreach ($object->{$indexProperty} as &$property) {
            $property = BaseValidator::createFromObject($property);
        }

        $validator = new $class(...$object->{$indexProperty});

        return $validator;
    }

    /**
     * Create validator from validation object
     * @param object $object Validation object
     * @return BaseValidator
     * @todo cleanup class loading
     * @throws \Exception When validator class cannot be found for object's type
     */
    public static function createFromObject($object)
    {
        $isFromBase = (static::class === self::class);

        if (!is_object($object)) {
            throw new \Exception('Expecting an object');
        }

        //Test type if it's set
        if (property_exists($object, 'type') && !empty($object->type)) {
            if (array_key_exists($object->type, self::$validatorRegistry)) {
                if (is_object($object->type) || is_array($object->type) || $object->type === null) {
                    throw new \Exception('Expecting string for type');
                }
                $className = self::$validatorRegistry[$object->type];
                $validator = new $className();
            /*} elseif (class_exists(__NAMESPACE__ . '\\' . $object->type)) {
                //if already loaded
                $className = __NAMESPACE__ . '\\' . $object->type;
                $validator = new $className();
            } elseif (class_exists(__NAMESPACE__ . '\\' . $object->type . 'Validator')) {
                //if already loaded
                $className = __NAMESPACE__ . '\\' . $object->type . 'Validator';
                $validator = new $className();*/
            } else {
                $className = $object->type . 'Validator';

                try {
                    //prevent fatal error
                    new \ReflectionClass($className);
                    //attempt to create class
                    $validator = new $className();
                } catch (\Exception $e) {
                    //Wont catch the fatal error
                    throw new \Exception(sprintf(
                        'Incorrect type "%s" from "%s"',
                        $object->type,
                        static::class
                    ));
                }
            }
        } elseif (($validator = static::createFromObjectForAdditional($object)) !== null) {
            return $validator;
        } elseif (!$isFromBase
            || (isset($object->type) && !empty($object->type) && $object->type == static::$type)
        ) {
            $validator = new static();
        } else {
            throw new \Exception(sprintf(
                'Type is required when creating from "%s"',
                self::class
            ));
        }

        //For each Validator's attribute
        foreach (array_merge(
            $validator::getTypeAttributes(),
            $validator::$commonAttributes
        ) as $attribute) {
            //Check if provided object contains this attribute
            if (property_exists($object, $attribute)) {
                if ($attribute == 'properties') {
                    //get properties as array
                    $properties = $object->{$attribute};

                    $createdProperties = new \stdClass();

                    foreach ($properties as $key => $property) {
                        //TODO remove
                        //if (!is_object($property)) {
                        //    throw new \Exception(sprintf(
                        //        'Expected object for property value "%s"',
                        //        $key
                        //    ));
                        //}

                        $createdProperties->{$key} =
                        BaseValidator::createFromObject($property);
                    }
                    //push to class
                    $validator->{$attribute} = $createdProperties;
                } elseif ($attribute == 'items' || $attribute == 'not') {
                    $validator->{$attribute} = BaseValidator::createFromObject(
                        $object->{$attribute}
                    );
                } else {
                    //Use attributes value in Validator object
                    $validator->{$attribute} = $object->{$attribute};
                }
            }
        }

        return $validator;
    }

    /**
     * Create validator from validation array
     * @param  array $array Validation array
     * @return BaseValidator
     */
    public static function createFromArray($array)
    {
        $object = (object)($array);
        return static::createFromObject($object);
    }

    /**
     * Create validator from validation object encoded as json object
     * @param  string $json Validation json encoded object
     * @return BaseValidator
     * @throws \Exception when JSON sting is not well formed
     */
    public static function createFromJSON($json)
    {
        $object = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'JSON parse had errors - ' . json_last_error_msg()
            );
        }

        return static::createFromObject($object);
    }

    /**
     * Export validator to json encoded string
     * @param boolean $JSON_PRETTY_PRINT *[Optional]*
     * @return string
     */
    public function toJSON($JSON_PRETTY_PRINT = false)
    {
        $object = $this->toObject();

        /*foreach ($object as $key => &$attribute) {
            //Check if any of attributes is an instance of BaseValidator
            if (is_object($attribute) && is_a($attribute, BaseValidator::class)) {
                $attribute = $attribute->toObject();
            }
        }*/

        return json_encode(
            $object,
            ($JSON_PRETTY_PRINT ? JSON_PRETTY_PRINT : 0)
        );
    }

    /**
     * Export validator to object
     * @return object
     */
    public function toObject()
    {
        $object = $this->toArray();

        //fix type to object
        if (isset($object['properties'])) {
            $object['properties'] = (object)$object['properties'];
        }

        foreach (['anyOf', 'allOf', 'oneOf', 'not'] as $property) {
            //fix type to object
            if (isset($object[$property])) {
                foreach ($object[$property] as &$propertyItem) {
                    $propertyItem = (object)$propertyItem;
                }
            }
        }

        return (object)$object;
    }

    /**
     * Export validator to array
     * @return array
     */
    public function toArray()
    {
        $object = [];

        if (static::$type) {
            $object['type'] = static::$type;
        }

        $attributes = array_merge(
            static::getTypeAttributes(),
            static::$commonAttributes
        );

        foreach ($attributes as $attribute) {
            $value = $this->{$attribute};

            if ($value !== null) {
                $toCopy = $value;

                //Clone object to prevent original object to be changed
                if (is_object($value)) {
                    $toCopy = clone $value;
                }

                $object[$attribute] = $toCopy;
            }

            if (static::$type == 'object' && $attribute == 'properties') {
                foreach ($object[$attribute] as $key => $property) {
                    if ($property instanceof BaseValidator) {
                        $object[$attribute]->{$key} = $property->toArray();
                    }
                }
                //fix type to array
                $object[$attribute] = (array)$object[$attribute];
            } elseif (in_array($attribute, ['allOf', 'anyOf', 'oneOf'])) {
                if (isset($object[$attribute]) && $object[$attribute] !== null) {
                    foreach ($object[$attribute] as $key => $property) {
                        if ($property instanceof BaseValidator) {
                            $object[$attribute][$key] = $property->toArray();
                        }
                    }
                }
            } elseif (in_array($attribute, ['items', 'not'])) {
                if (isset($object[$attribute])
                    && $object[$attribute]
                    && $object[$attribute] instanceof BaseValidator
                ) {
                    $object[$attribute] = $object[$attribute]->toArray();
                }
            }
        }

        return $object;
    }
}
