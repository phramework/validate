<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis.
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

use Phramework\Exceptions\IncorrectParametersException;

/**
 * BaseValidator, every validator **MUST** extend this class.
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 *
 * @since 0.0.0
 */
abstract class BaseValidator
{
    /**
     * Validator's type
     * Must be overwriten, default is 'string'.
     *
     * @var string|null
     */
    protected static $type = null;

    /**
     * This static method will instanciate a new object as validation model
     * to parse the input value.
     *
     * @param mixed $value Input value to validate
     */
    public static function parseStatic($value)
    {
        $validationObject = new static();

        return $validationObject->parse($value);
    }

    /**
     * Validate value.
     *
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     *
     * @param mixed $value Input value to validate
     *
     * @return ValidateResult
     */
    abstract public function validate($value);

    /**
     * Common helper method to validate against all common keywords.
     *
     * @uses validateEnum
     *
     * @param mixed          $value  Value to validate
     * @param ValidateResult $return Current ValidateResult status
     *
     * @return ValidateResult
     */
    protected function validateCommon($value, $validateResult)
    {
        //While current status of validation is true,
        //keep validating against common keywords

        //validate against enum using validateEnum
        if ($validateResult->status === true) {
            $validateEnum = $this->validateEnum($value);

            if ($validateEnum->status !== true) {
                return $validateEnum;
            }
        }

        return $validateResult;
    }

    /**
     * Common helper method to validate against "enum" keyword.
     *
     * @see 5.5.1. enum http://json-schema.org/latest/json-schema-validation.html#anchor75
     *
     * @param mixed $value Value to validate
     *
     * @return ValidateResult
     *
     * @todo provide support for objects and arrays
     */
    protected function validateEnum($value)
    {
        $return = new ValidateResult($value, false);

        //Check if $this->enum is set and it's not null since its optional
        if ($this->enum && $this->enum !== null) {
            if (is_object($value)) {
                throw new \Exception('Objects are not supported');
            }

            //Search current $value in enum
            foreach ($this->enum as $v) {
                if (is_object($v)) {
                    throw new \Exception('Objects are not supported');
                }

                if ($value == $v) {
                    if ($this->validateType && $this->validateType
                        && gettype($value) !== gettype($v)) {
                        //ignore that the value is found
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
            $return->errorObject = new IncorrectParametersException([[
                'type'    => static::getType(),
                'failure' => 'enum',
            ]]);
        } else {
            //Ignored validation, set status to true
            $return->status = true;
        }

        return $return;
    }

    /**
     * Get validator's type.
     *
     * @return string|null
     */
    public static function getType()
    {
        return static::$type;
    }

    /**
     * Validator's attributes
     * Can be overwriten.
     *
     * @var string[]
     */
    protected static $typeAttributes = [
    ];

    /**
     * Common valdator attributes.
     *
     * @var string[]
     */
    protected static $commonAttributes = [
        'title',
        'description',
        'default',
        'format',
        'enum',
        'validateType', //non standard attribute, can be used in combination with enum
    ];

    public $default;

    /**
     * Get validator's attributes.
     *
     * @return string[]
     */
    public static function getTypeAttributes()
    {
        return static::$typeAttributes;
    }

    /**
     * Objects current attributes and values.
     *
     * @var array
     */
    protected $attributes = [];

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
     * Get attribute's value.
     *
     * @param string $key Attribute's key
     *
     * @throws \Exception If key not found
     *
     * @return mixed
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
     * Set attribute's value.
     *
     * @param string $key   Attribute's key
     * @param mixed  $value Attribute's value
     *
     * @throws \Exception If key not found
     *
     * @return BaseValidator Return's this validator object
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
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array|null $enum
     */
    public function setEnum($enum)
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * This method use this validator to parse data from $value argument
     * and return a clean object.
     *
     * @param mixed $value Input value to validate
     *
     * @throws \Phramework\Exceptions\MissingParametersException
     * @throws \Phramework\Exceptions\IncorrectParametersException
     *
     * @return mixed
     */
    public function parse($value)
    {
        $validateResult = $this->validate($value);

        if (!$validateResult->status) {
            throw $validateResult->errorObject;
        }

        $castedValue = $validateResult->value;

        return $castedValue;
    }

    /**
     * Validator classes registry.
     *
     * @var string[]
     */
    private static $validatorRegistry = [
        'string'            => StringValidator::class,
        'url'               => URLValidator::class,
        'integer'           => IntegerValidator::class,
        'int'               => IntegerValidator::class, //alias
        'unsignedinteger'   => UnsignedIntegerValidator::class,
        'uinteger'          => UnsignedIntegerValidator::class, //alias
        'uint'              => UnsignedIntegerValidator::class, //alias
        'date-time'         => DatetimeValidator::class,
    ];

    /**
     * Register a custom validator for a type.
     *
     * @param string $type      Type's name, `x-` SHOULD be used for custom types
     * @param string $className Validator's full classname.
     *                          All validators MUST extend `BaseValidator` class
     *
     * @example
     * ```php
     * BaseValidator::registerValidator('x-address', 'My\APP\AddressValidator');
     * ```
     *
     * @throws Exception
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
            self::class,
            true
        )) {
            throw new \Exception(sprintf(
                '"className" MUST extend "%s"',
                self::class
            ));
        }

        self::$validatorRegistry[$type] = $className;
    }

    /**
     * Helper method.
     * Used to create anyOf, allOf and oneOf validators from objects.
     *
     * @param \stdClass $object Validation object
     *
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
            return;
        }

        //Parse index property's object as validator
        foreach ($object->{$indexProperty} as &$property) {
            $property = self::createFromObject($property);
        }

        $validator = new $class($object->{$indexProperty});

        return $validator;
    }

    /**
     * Create validator from validation object.
     *
     * @param \stdClass $object Validation object
     *
     * @throws \Exception When validator class cannot be found for object's type
     *
     * @return BaseValidator
     *
     * @todo cleanup class loading
     */
    public static function createFromObject($object)
    {
        $isFromBase = (static::class === self::class);

        //Test type if it's set
        if (property_exists($object, 'type')) {
            if (array_key_exists($object->type, self::$validatorRegistry)) {
                $className = self::$validatorRegistry[$object->type];
                $validator = new $className();
            } elseif (class_exists(__NAMESPACE__.'\\'.$object->type)) {
                //if already loaded
                $className = __NAMESPACE__.'\\'.$object->type;
                $validator = new $className();
            } elseif (class_exists(__NAMESPACE__.'\\'.$object->type.'Validator')) {
                //if already loaded
                $className = __NAMESPACE__.'\\'.$object->type.'Validator';
                $validator = new $className();
            } else {
                $className = $object->type.'Validator';

                try {
                    //prevent fatal error
                    new \ReflectionClass($className);
                    //attempt to create class
                    $validator = new $className();
                } catch (\Exception $e) {
                    //Wont catch the fatal error
                    throw new \Exception(sprintf(
                        'Incorrect type %s from %s',
                        $object->type,
                        static::class
                    ));
                }
            }
        } elseif (($validator = static::createFromObjectForAdditional($object)) !== null) {
            return $validator;
        } elseif (!$isFromBase || $object->type == static::$type) {
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
                        if (!is_object($property)) {
                            throw new \Exception(sprintf(
                                'Expected object for property value "%"',
                                $key
                            ));
                        }

                        $createdProperties->{$key} =
                        self::createFromObject($property);
                    }
                    //push to class
                    $validator->{$attribute} = $createdProperties;
                } elseif ($attribute == 'items') {
                    $validator->{$attribute} = self::createFromObject(
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
     * Create validator from validation array.
     *
     * @param array $object Validation array
     *
     * @return BaseValidator
     */
    public static function createFromArray($array)
    {
        $object = (object) ($array);

        return static::createFromObject($object);
    }

    /**
     * Create validator from validation object encoded as json object.
     *
     * @param string $object Validation json encoded object
     *
     * @throws Exception when JSON sting is not well formed
     *
     * @return BaseValidator
     */
    public static function createFromJSON($json)
    {
        $object = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'JSON parse had errors - '.json_last_error_msg()
            );
        }

        return static::createFromObject($object);
    }

    /**
     * Export validator to json encoded string.
     *
     * @return string
     */
    public function toJSON($JSON_PRETTY_PRINT = false)
    {
        $object = $this->toObject();

        foreach ($object as $key => &$attribute) {
            //Check if any of attributes is an instance of BaseValidator
            if (is_object($attribute) && is_a($attribute, self::class)) {
                $attribute = $attribute->toObject();
            }
        }

        return json_encode(
            $object,
            ($JSON_PRETTY_PRINT ? JSON_PRETTY_PRINT : 0)
        );
    }

    /**
     * Export validator to object.
     *
     * @return \stdClass
     */
    public function toObject()
    {
        $object = $this->toArray();

        //fix type to object
        if (isset($object['properties'])) {
            $object['properties'] = (object) $object['properties'];
        }

        foreach (['anyOf', 'allOf', 'oneOf'] as $property) {
            //fix type to object
            if (isset($object[$property])) {
                foreach ($object[$property] as &$propertyItem) {
                    $propertyItem = (object) $propertyItem;
                }
            }
        }

        return (object) $object;
    }

    /**
     * Export validator to array.
     *
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
                    if ($property instanceof self) {
                        $object[$attribute]->{$key} = $property->toArray();
                    }
                }
                //fix type to array
                $object[$attribute] = (array) $object[$attribute];
            } elseif (in_array($attribute, ['allOf', 'anyOf', 'oneOf'])) {
                foreach ($object[$attribute] as $key => $property) {
                    if ($property instanceof self) {
                        $object[$attribute][$key] = $property->toArray();
                    }
                }
            }
        }

        return $object;
    }
}
