<?php
/**
 * Copyright 2015-2019 Xenofon Spafaridis
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

use Phramework\Validate\Result\Result;
use Phramework\Exceptions\IncorrectParameterException;

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

    /**
     * @param bool|null $exclusiveMinimum When null will behave as false
     * @param bool|null $exclusiveMaximum When null will behave as false
     * @throws \DomainException When minimum is not less than the maximum
     */
    public function __construct(
        ?float $minimum = null,
        ?float $maximum = null,
        ?bool $exclusiveMinimum = false,
        ?bool $exclusiveMaximum = false,
        ?float $multipleOf = null
    ) {
        parent::__construct();

        if ($maximum !== null && $minimum !== null && $maximum < $minimum) {
            throw new \DomainException('maximum cant be less than minimum');
        }

        if ($multipleOf !== null && $multipleOf <= 0) {
            throw new \InvalidArgumentException('multipleOf must be a positive number');
        }

        if ($exclusiveMinimum && $minimum === null) {
            throw new \DomainException('If "exclusiveMinimum" is set to true, "minimum" MUST also be set');
        }

        if ($exclusiveMaximum && $maximum === null) {
            throw new \DomainException('If "exclusiveMaximum" is set to true, "maximum" MUST also be set');
        }

        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->exclusiveMaximum = $exclusiveMaximum;
        $this->multipleOf = $multipleOf;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\Result for Result object
     * @param  mixed $value Value to validate
     * @return Result
     */
    public function validate($value)
    {
        $return = $this->validateCommon($value, new Result($value, true));

        if ($return->status === false) {
            return $return;
        }

        return $this->validateNumber($return->value);
    }

    /**
     * Validate value, without calling validateCommon
     * @see \Phramework\Validate\Result for Result object
     * @param  mixed $value Value to validate
     * @return Result
     */
    protected function validateNumber($value)
    {
        $return = new Result($value, false);

        if (is_string($value)) {
            //Replace comma with dot
            $value = str_replace(',', '.', $value);
        }

        //Apply all rules
        if (!is_numeric($value) || filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            //error
            $return->exception = new IncorrectParameterException(
                'type',
                null,
                $this->source
            );
        } elseif ($this->maximum !== null
            && ($value > $this->maximum
                || ($this->exclusiveMaximum === true && $value >= $this->maximum)
            )
        ) {
            //error
            $return->exception = new IncorrectParameterException(
                'maximum',
                null,
                $this->source
            );
        } elseif ($this->minimum !== null
            && ($value < $this->minimum
                || ($this->exclusiveMinimum === true && $value <= $this->minimum)
            )
        ) {
            //error
            $return->exception = new IncorrectParameterException(
                'minimum',
                null,
                $this->source
            );
        } elseif ($this->multipleOf !== null
            && fmod((float)$value, (float)$this->multipleOf) != 0
        ) {
            //error
            $return->exception = new IncorrectParameterException(
                'multipleOf',
                null,
                $this->source
            );
        } else {
            $return->exception = null;

            //Set status to success
            $return->status = true;

            //Type cast
            $return->value = $this->cast($value);
        }

        return $return;
    }

    protected function cast($value)
    {
        return (float) $value;
    }
}
