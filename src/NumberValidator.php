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

/**
 * Number validator
 * @property float|null minimum
 * @property float|null maximum
 * @property boolean|null exclusiveMinimum
 * @property boolean|null exclusiveMaximum
 * @property float multipleOf
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class NumberValidator extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class attributes
     * @var array
     */
    protected static $typeAttributes = [
        'minimum',
        'maximum',
        'exclusiveMinimum',
        'exclusiveMaximum',
        'multipleOf'
    ];

    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'number';

    public function __construct(
        $minimum = null,
        $maximum = null,
        $exclusiveMinimum = null,
        $exclusiveMaximum = null,
        $multipleOf = null
    ) {
        parent::__construct();

        if ($minimum !== null && !is_numeric($minimum)) {
            throw new \Exception('Minimum must be numeric');
        }

        if ($maximum !== null && !is_numeric($maximum)) {
            throw new \Exception('Maximum must be numeric');
        }

        if ($maximum < $minimum) {
            throw new \Exception('maximum cant be less than minimum');
        }

        if ($exclusiveMinimum !== null && !is_bool($exclusiveMinimum)) {
            throw new \Exception('exclusiveMinimum must be boolean');
        }

        if ($exclusiveMaximum !== null && !is_bool($exclusiveMaximum)) {
            throw new \Exception('exclusiveMaximum must be boolean');
        }

        if ($multipleOf !== null && !is_numeric($multipleOf)) {
            throw new \Exception('multipleOf must be numeric');
        }

        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->exclusiveMaximum = $exclusiveMaximum;
        $this->multipleOf = $multipleOf;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        if (is_string($value)) {
            //Replace comma with dot
            $value = str_replace(',', '.', $value);
        }

        //Apply all rules
        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'type'
                ]
            ]);
        } elseif ($this->maximum !== null
            && ($value > $this->maximum
                || ($this->exclusiveMaximum === true && $value >= $this->maximum)
            )
        ) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'maximum'
                ]
            ]);
        } elseif ($this->minimum !== null
            && ($value < $this->minimum
                || ($this->exclusiveMinimum === true && $value <= $this->minimum)
            )
        ) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'minimum'
                ]
            ]);
        } elseif ($this->multipleOf !== null
            && ((float)$value % $this->multipleOf) !== 0
        ) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'multipleOf'
                ]
            ]);
        } else {
            $return->errorObject = null;
            //Set status to success
            $return->status = true;
            //Type cast
            $return->value  = (float)($value);
        }

        return $this->validateCommon($value, $return);
    }
}
