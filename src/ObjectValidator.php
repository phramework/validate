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

use Phramework\Exceptions\Source\ISource;
use Phramework\Exceptions\Source\Pointer;
use Phramework\Validate\Result\Result;
use Phramework\Exceptions\IncorrectParameterException;
use Phramework\Exceptions\IncorrectParametersException;
use Phramework\Exceptions\MissingParametersException;

/**
 * Object validator
 * @property integer        $minProperties Minimum number of properties
 * @property integer|null   $maxProperties Minimum number of properties
 * @property string[]       $required Required properties keys
 * @property object         $dependencies Dependencies
 * @property object         $properties Properties
 * @property object         $patternProperties
 * @property BaseValidator|boolean $additionalProperties
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor53
 * 5.4.  Validation keywords for objects
 * @since 0.0.0
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
        'x-visibility',
        'patternProperties'
    ];

    /**
     * @param \stdClass             $properties
     * Properties
     * @param string[]              $required
     * Required properties keys
     * @param BaseValidator|boolean   $additionalProperties
     * Default is null
     * @param integer               $minProperties
     * Default is 0
     * @param integer               $maxProperties
     * Default is null
     * @param \stdClass|null        $dependencies
     * @param \stdClass|null        $xVisibility
     * @param \stdClass             $patternProperties
     * x-visibility directive https://github.com/phramework/validate/issues/19
     * @throws \Exception
     */
    public function __construct(
        \stdClass $properties = null,
        array $required = [],
        $additionalProperties = true,
        int $minProperties = 0,
        int $maxProperties = null,
        \stdClass $dependencies = null,
        \stdClass $xVisibility = null,
        \stdClass $patternProperties = null
    ) {
        parent::__construct();

        $this->minProperties        = $minProperties;
        $this->maxProperties        = $maxProperties;
        $this->properties           = $properties;
        $this->required             = $required;
        $this->additionalProperties = $additionalProperties;
        $this->dependencies         = $dependencies;
        $this->{'x-visibility'}     = $xVisibility;
        $this->patternProperties    = $patternProperties;
    }

    /**
     * Validate value
     * @see Result for result object definition
     * @param  object $value Value to validate
     * @return Result
     * @todo clean up failure of recursive objects
     */
    public function validate($value)
    {
        $return = new Result($value, false);

        $failure = null;

        $details = null;
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
        $evaluate = function (
            \stdClass $propertyValues,
            array $expression
        ) use (&$evaluate) {
            $functions = (object) [
                /**
                 * @return bool
                 */
                'member' => function (
                    string $operator,
                    string $propertyKey,
                    array  $memberValues
                ) use ($propertyValues) {
                    if (!property_exists($propertyValues, $propertyKey)) {
                        return false;
                    }

                    $propertyValue = $propertyValues->{$propertyKey};

                    return in_array($propertyValue, $memberValues);
                },
                'or' => function (
                    string $operator,
                    array ...$list
                ) use (
                    $propertyValues,
                    &$evaluate
                ) {
                    return array_reduce(
                        $list,
                        function (
                            bool $carry,
                            array $item
                        ) use (
                            $propertyValues,
                            $evaluate
                        ) {
                            return $carry || $evaluate($propertyValues, $item);
                        },
                        false
                    );
                }
            ];

            $functionKey = $expression[0];

            //is atom (bool)
            if (is_bool($functionKey)) {
                return $functionKey;
            }

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

                    //remove from required
                    if (!$evaluation) {
                        $this->required = array_diff(
                            $this->required,
                            [$propertyKey]
                        );
                    }

                    //if is defined and evaluation is false throw exception
                    if (!$evaluation && isset($value->{$propertyKey})) {
                        $return->exception = new IncorrectParameterException(
                            'x-visibility',
                            'Property defined although x-visibility criteria are not met',
                            $this->expandPointerSource(
                                $propertyKey,
                                $this->getSource()
                            )
                        );

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
                $return->exception = new MissingParametersException(
                    $missingProperties,
                    $this->getSource()
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
                    if (!$propertyValidateResult->exception) {
                        $errorObjects[$key] = [];
                    } else {
                        switch (get_class($propertyValidateResult->exception)) {
                            //case MissingParametersException::class:
                               // $missingObjects[$key] = $propertyValidateResult->exception->getParameters();
                                //break;
                            case IncorrectParameterException::class:
                                //$errorObjects[$key] = $propertyValidateResult->exception;
                            case IncorrectParametersException::class:
                                //$errorObjects[$key] = $propertyValidateResult->exception;
                                //$errorObjects[$key] = $propertyValidateResult->exception->getParameters();
                                //break;
                            default:
                                $errorObjects[$key] = $propertyValidateResult->exception;
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
            $return->status = false;

//            //error
//            $errorObject = [];

            /*if (!empty($errorObjects)) {
                $errorObject[] =  new IncorrectParameterException(
                    'properties',
                    null,
                    $this->getSource()
                );
                 //'properties' => $errorObjects

            }*/

            /*if (!empty($missingObjects)) {
                $errorObject[] =  new IncorrectParameterException(
                    'missing',
                    null,
                    $this->getSource()
                );

                // 'properties' => $missingObjects

            }*/

            /*if (!empty($missingDependencies)) {
                //todo source
                //todo use //'properties' => $missingDependencies
                $errorObject[] = new IncorrectParameterException(
                    'dependencies',
                    null,
                    $this->getSource()
                );
            }*/

            if (!empty($missingDependencies)) {
                $return->exception = new MissingParametersException(
                    $missingDependencies,
                    $this->getSource()
                );
            } elseif (!empty($missingObjects)) {
                $return->exception = new MissingParametersException(
                    $missingObjects,
                    $this->getSource()
                );
            } else {
                $return->exception = new IncorrectParametersException(
                    ...array_values($errorObjects)
                );
            }

            return $return;
        }

        //Check if additionalProperties are set
        $foundAdditionalProperties = [];

        //Search for value properties not defined in validator properties
        foreach ($valueProperties as $key => $property) {
            if (!property_exists($this->properties, $key)) {
                $foundAdditionalProperties[] = $key;
            }
        }

        if (!empty($foundAdditionalProperties)) {
            if ($this->patternProperties !== null) {
                //for each additional property
                foreach ($foundAdditionalProperties as $key) {
                    //search key patterns
                    foreach ($this->patternProperties as $k => $patternValidator) {
                        $pattern = '/' . $k . '/';

                        if (preg_match($pattern, preg_quote($key, '/'))) { //if found
                            //remove key from additional
                            $foundAdditionalProperties = array_diff(
                                $foundAdditionalProperties,
                                [$key]
                            );

                            //parse
                            $patternValidator->setSource(
                                $this->expandPointerSource($key, $this->getSource())
                            );

                            $propertyValidateResult = $patternValidator->validate(
                                $value->{$key}
                            );

                            //todo qwertgds
                            if ($propertyValidateResult->status === false) {
                                $return->exception = $propertyValidateResult->exception;
                                return $return;
                            }

                            $value->{$key} = $propertyValidateResult->value;

                            break;
                        }
                    }
                }
            }

            //continue with what remains from patternProperties
            if ($this->additionalProperties === false) {
                $return->exception = new IncorrectParameterException(
                    'additionalProperties',
                    'additional properties found: ' . implode(',', $foundAdditionalProperties),
                    $this->getSource()
                );

                //todo 'properties' => $foundAdditionalProperties
                return $return;
            } elseif ($this->additionalProperties !== true) { //eg is BaseValidator
                //validate against $additionalProperties
                foreach ($foundAdditionalProperties as $key) {
                    $this->additionalProperties->setSource(
                        $this->expandPointerSource($key, $this->getSource())
                    );

                    $propertyValidateResult = $this->additionalProperties->validate(
                        $value->{$key}
                    );

                    //todo qwertgds
                    if ($propertyValidateResult->status === false) {
                        $return->exception = $propertyValidateResult->exception;
                        /*$return->exception = new IncorrectParametersException(
                            new IncorrectParameterException(

                            ),
                            $propertyValidateResult->exception
                        )*/
                        return $return;
                    }

                    $value->{$key} = $propertyValidateResult->value;
                }
            }
        }

        //success
        $return->status = true;

        //Apply type casted
        $return->value = $value;

        return $this->validateCommon($value, $return);

        err:
        $return->exception = new IncorrectParameterException(
            $failure,
            $details,
            $this->source
        );

        return $this->validateCommon($value, $return);
    }

    /**
     * Add source to child properties
     * @param BaseValidator $property
     * @param string        $key
     */
    protected function expandSource(BaseValidator &$property, string $key)
    {
        if ($this->getSource() !== null && get_class($this->getSource()) == Pointer::class) {
            //If it does not have a source already
            if ($property->getSource() === null) {
                $property->setSource(
                    $this->expandPointerSource(
                        $key,
                        $this->getSource()
                    )
                );
            }
        }
    }

    /**
     * @todo
     * Expand source
     * @param ISource       $source
     * @param string        $key
     * @return ISource
     */
    public function expandPointerSource(string $key, ISource $source = null)
    {
        if ($source !== null && get_class($source) === Pointer::class) {
            return new Pointer(
                $source->getPath() . '/' . $key
            );
        }
        
        return $source;
    }

    /**
     * Override base set source in order to set source for object's properties,
     * works only of Pointer source
     * @param ISource $source
     * @return $this
     */
    public function setSource(ISource $source = null)
    {
        parent::setSource($source);

        foreach ($this->properties as $key => &$property) {
            $this->expandSource($property, $key);
        }

        return $this;
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
     * @return $this
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
     * Add a property to this object validator, if key exists it will overwrite
     * @param string $key
     * @param BaseValidator $property
     * @return $this
     */
    public function addProperty(string $key, BaseValidator $property)
    {
        //expand source
        $this->expandSource($property, $key);

        //Add this key, value to
        $this->properties->{$key} = $property;

        return $this;
    }
    
    public function __set($key, $value)
    {
        switch ($key) {
            case 'properties':
                if ($value === null) {
                    $value = new \stdClass();
                }

                if (!is_object($value)) {
                    throw new \Exception('"properties" must be object');
                }
                break;
            case 'additionalProperties':
                if (!is_bool($value)
                    && !is_subclass_of($value, BaseValidator::class)
                ) {
                    throw new \Exception(
                        'Only bool and BaseValidator instances are allowed for "additionalProperties"'
                    );
                }
                break;
            case 'minProperties':
                if (!is_int($value) || $value < 0) {
                    throw new \Exception('"minProperties" must be positive integer');
                }
                break;
            case 'maxProperties':
                if ($value !== null) {
                    if (!is_int($value) || $value < 0) {
                        throw new \Exception(
                            '"maxProperties" must be positive integer'
                        );
                    } elseif ($value < $this->minProperties) {
                        throw new \Exception(
                            '"maxProperties" must be greater or equal to "minProperties'
                        );
                    }
                }
                break;
            case 'dependencies':
                if ($value == null) {
                    $value = new \stdClass();
                } else {
                    if (!is_object($value)) {
                        throw new \Exception('"dependencies" must be object');
                    }

                    foreach ($value as $k => $v) {
                        if (!is_array($v)) {
                            throw new \Exception(
                                '"dependencies" member values must be arrays'
                            );
                        }
                    }
                }
                break;
            case 'xVisibility':
                if ($value !== null) {
                    if (!is_object($value)) {
                        throw new \Exception('"x-visibility" must be object');
                    }

                    foreach ($value as $k => $v) {
                        if (!is_array($v)) {
                            throw new \Exception(
                                '"x-visibility" member values must be arrays'
                            );
                        }
                    }
                }
                break;
        }
        
        return parent::__set($key, $value);
    }
}
