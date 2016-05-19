<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis
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

use \Phramework\Validate\ValidateResult;
use \Phramework\Exceptions\IncorrectParametersException;
use \Phramework\Exceptions\MissingParametersException;

/**
 * Object validator
 * @property integer        $minProperties Minimum number of properties
 * @property integer|null   $maxProperties Minimum number of properties
 * @property string[]       $required Required properties keys
 * @property object         $dependencies Dependencies
 * @property object $properties Properties
 * @property object|boolean|null $additionalProperties
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor53
 * 5.4.  Validation keywords for objects
 * @since 0.0.0
 * @todo Implement patternProperties
 * @todo Implement additionalProperties "additionalProperties": { "type": "string" }
 */
class ObjectValidator extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'object';

    protected static $typeAttributes = [
        'minProperties',
        'maxProperties',
        'required',
        'properties',
        'additionalProperties',
        'dependencies',
        'x-visibility'
    ];

    /**
     * @param object                $properties
     * *[Optional]* Properties
     * @param string[]              $required
     * *[Optional]* Required properties keys
     * @param object|boolean|null   $additionalProperties
     * *[Optional]* Default is null
     * @param integer               $minProperties
     * *[Optional]* Default is 0
     * @param integer               $maxProperties
     * *[Optional]* Default is null
     * @param object|null           $dependencies
     * @throws \Exception
     */
    public function __construct(
        $properties = [],
        $required = [],
        $additionalProperties = null,
        $minProperties = 0,
        $maxProperties = null,
        $dependencies = null,
        $xVisibility = null
    ) {
        parent::__construct();

        //Work with objects
        if (is_array($properties)) {
            $properties = (object)$properties;
        }

        if (!is_int($minProperties) || $minProperties < 0) {
            throw new \Exception('minProperties must be positive integer');
        }

        if (($maxProperties !== null && (!is_int($maxProperties)) || $maxProperties < $minProperties)) {
            throw new \Exception('maxProperties must be positive integer');
        }

        if ($additionalProperties !== null && !is_bool($additionalProperties)) {
            throw new \Exception('For now only boolean values supported for "additionalProperties"');
        }

        if ($dependencies == null) {
            $dependencies = new \stdClass();
        } else {
            if (!is_object($dependencies)) {
                throw new \Exception('dependencies must be object');
            }

            foreach ($dependencies as $key => $value) {
                if (!is_array($value)) {
                    throw new \Exception('dependencies member values must be arrays');
                }
            }
        }

        if ($xVisibility !== null) {
            if (!is_object($xVisibility)) {
                throw new \Exception('x-visibility must be object');
            }

            foreach ($xVisibility as $key => $value) {
                if (!is_array($value)) {
                    throw new \Exception('visibility member values must be arrays');
                }
            }
        }

        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;

        $this->properties = $properties;
        $this->required = $required;
        $this->additionalProperties = $additionalProperties;
        $this->dependencies = $dependencies;
        $this->{'x-visibility'} = $xVisibility;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  object $value Value to validate
     * @return ValidateResult
     * @todo clean up failure of recursive objects
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);
        $failure = null;

        if (!is_object($value)) {
            $failure = 'type';
            //error
            goto err;
        }

        $valueProperties = get_object_vars($value);

        $valuePropertiesCount = count($valueProperties);

        if ($valuePropertiesCount < $this->minProperties) {
            //error
            $failure = 'minProperties';
            goto err;
        } elseif ($this->maxProperties !== null
            && $valuePropertiesCount > $this->maxProperties
        ) {
            $failure = 'maxProperties';
            //error
            goto err;
        }

        /**
         * @param \stdClass $propertyValues
         * @param array $expression
         * @return bool
         * @throws \Exception When an unknown function is set
         */
        $evaluate = function (\stdClass $propertyValues, array $expression) {
            $functions = (object) [
                /**
                 * @return bool
                 */
                'member' => function (
                    string $operator,
                    string $propertyKey,
                    array  $memberValues
                ) use ($propertyValues, &$functions) {
                    if (!property_exists($propertyValues, $propertyKey)) {
                        return false;
                    }

                    $propertyValue = $propertyValues->{$propertyKey};

                    return in_array($propertyValue, $memberValues);
                }
            ];

            $functionKey = $expression[0];

            if (!isset($functions->{$functionKey})) {
                throw new \Exception(
                    'Unknown function "%s"',
                    $functionKey
                );
            }

            $function = $functions->{$functionKey};

            return $function(...$expression);
        };

        //validate x-visibility
        if ($this->{'x-visibility'} !== null) {
            $xVisibility = $this->{'x-visibility'};

            foreach ($xVisibility as $propertyKey => $expression) {
                if (!property_exists($value, $propertyKey)) {
                    $this->required = array_diff(
                        $this->required,
                        [$propertyKey]
                    );
                } else {

                    //evaluate if property is visible
                    $evaluation = $evaluate(
                        $value,
                        $expression
                    );

                    /*//remove from required
                    if (!$evaluation) {
                        var_dump($this->required);

                        $this->required = array_diff(
                            $this->required,
                            [$propertyKey]
                        );

                        var_dump($this->required);
                    }*/

                    //if is defined and evaluation is false throw exception
                    if (!$evaluation && isset($value->{$propertyKey})) {

                        $return->errorObject = new IncorrectParametersException([
                            'type' => static::getType(),
                            'failure' => 'x-visibility',
                            'properties' => [$propertyKey]
                        ]);

                        return $return;
                    }
                }
            }
        }

        //Check if required properties are set and find if any of them are missing
        if ($this->required !== null || !empty($this->required)) {
            //Find missing properties
            $missingProperties = [];

            foreach ($this->required as $key) {
                if (!array_key_exists($key, $valueProperties)) {
                    //Push key to missing
                    $missingProperties[] = $key;
                }
            }

            if (!empty($missingProperties)) {
                //error, missing properties
                $return->errorObject = new MissingParametersException(
                    $missingProperties
                );
                return $return;
            }
        }

        //Return default if it's empty
        if ($valuePropertiesCount === 0 && property_exists($this, 'default')) {
            $return->value = $this->default;
            $return->status = true;
            return $return;
        }

        $overallPropertyStatus = true;
        $errorObjects = [];
        $missingObjects = [];
        $missingDependencies = [];

        //Validate all validator's properties
        foreach ($this->properties as $key => $property) {
            //If this property key exists in given $value, validate it
            if (array_key_exists($key, $valueProperties)) {
                $propertyValue = $valueProperties[$key];
                $propertyValidateResult = $property->validate($propertyValue);

                //Check dependencies, if key is set, it's dependencies must also be set
                if (isset($this->dependencies->{$key})) {
                    foreach ($this->dependencies->{$key} as $dependencyKey) {
                        if (!array_key_exists($dependencyKey, $valueProperties)) {
                            $missingDependencies[] = $dependencyKey;
                            //set status to false
                            $overallPropertyStatus = false;
                        }
                    }
                }

                //Determine $overallPropertyStatus
                $overallPropertyStatus = $overallPropertyStatus && $propertyValidateResult->status;

                if (!$propertyValidateResult->status) {
                    if (!$propertyValidateResult->errorObject) {
                        $errorObjects[$key] = [];
                    } else {
                        switch (get_class($propertyValidateResult->errorObject)) {
                            case MissingParametersException::class:
                                $missingObjects[$key] = $propertyValidateResult->errorObject->getParameters();
                                break;
                            case IncorrectParametersException::class:
                                $errorObjects[$key] = $propertyValidateResult->errorObject->getParameters();
                                break;
                            default:
                                $errorObjects[$key] = [];
                        }
                    }
                }

                //use type casted value
                $value->{$key} = $propertyValidateResult->value;
            } elseif (property_exists($property, 'default')) {
                //Else use default property's value
                $value->{$key} = $property->default;
            }
        }

        if (!$overallPropertyStatus) {
            $return->status = $overallPropertyStatus;

            //error
            $errorObject = [];

            if (!empty($errorObjects)) {
                $errorObject[] = [
                 'type' => static::getType(),
                 'failure' => 'properties',
                 'properties' => $errorObjects
                ];
            }

            if (!empty($missingObjects)) {
                $errorObject[] = [
                 'type' => static::getType(),
                 'failure' => 'missing',
                 'properties' => $missingObjects
                ];
            }

            if (!empty($missingDependencies)) {
                $errorObject[] = [
                    'type' => static::getType(),
                    'failure' => 'dependencies',
                    'properties' => $missingDependencies
                ];
            }

            if (!empty($missingDependencies)) {
                $return->errorObject = new MissingParametersException(
                    $missingDependencies
                );
            } elseif (!empty($missingObjects)) {
                $return->errorObject = new MissingParametersException(
                    $missingObjects
                );
            } else {
                $return->errorObject = new IncorrectParametersException(
                    $errorObject
                );
            }

            return $return;
        }

        //Check if additionalProperties are set
        if ($this->additionalProperties === false) {
            $foundAdditionalProperties = [];

            foreach ($valueProperties as $key => $property) {
                if (!property_exists($this->properties, $key)) {
                    $foundAdditionalProperties[] = $key;
                }
            }

            if (!empty($foundAdditionalProperties)) {
                $return->errorObject = new IncorrectParametersException([[
                    'type' => static::getType(),
                    'failure' => 'additionalProperties',
                    'properties' => $foundAdditionalProperties
                ]]);
                return $return;
            }
        }

        //success
        $return->status = true;

        //Apply type casted
        $return->value = $value;

        return $this->validateCommon($value, $return);

        err:
        $return->errorObject = new IncorrectParametersException([
            'type' => static::getType(),
            'failure' => $failure
        ]);

        return $this->validateCommon($value, $return);
    }

    /**
     * This method use this validator to parse data from $value argument
     * and return a clean object
     * @param  array|object $value Input value to validate
     * @throws \Phramework\Exceptions\MissingParametersException
     * @throws \Phramework\Exceptions\IncorrectParametersException
     * @return object
     * @todo find out if MissingParameters
     * @todo add errors
     * @todo additionalProperties
     */
    public function parse($value)
    {
        if (is_array($value)) {
            $value = (object) $value;
        }

        return parent::parse($value);
    }

    /**
     * Add properties to this object validator
     * @param array||object $properties [description]
     * @throws \Exception If properties is not an array
     */
    public function addProperties($properties)
    {
        if (empty($properties) || !count((array)$properties)) {
            throw new \Exception('Empty properties given');
        }

        if (!is_array($properties) && !is_object($properties)) {
            throw new \Exception('Expected array or object');
        }

        foreach ($properties as $key => $property) {
            $this->addProperty($key, $property);
        }

        return $this;
    }

    /**
     * Add a property to this object validator
     * @param BaseValidator $property
     * @throws \Exception If property key exists
     */
    public function addProperty($key, BaseValidator $property)
    {
        if (property_exists($this->properties, $key)) {
            throw new \Exception('Property key exists');
        }

        //Add this key, value to
        $this->properties->{$key} = $property;

        return $this;
    }
}
