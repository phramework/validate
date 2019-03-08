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

use Phramework\Validate\Result\Result;
use \Phramework\Exceptions\IncorrectParametersException;

/**
 * Integer validator
 * @uses \Phramework\Validate\Number As base implementation's rules to
 * validate that the value is a number and then applies additional rules
 * to validate that this is a interger
 * @property integer|null minimum
 * @property integer|null maximum
 * @property boolean|null exclusiveMinimum
 * @property boolean|null exclusiveMaximum
 * @property integer multipleOf
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class IntegerValidator extends \Phramework\Validate\NumberValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'integer';

    /**
     * @param integer|null $minimum
     * @param integer|null $maximum
     * @param boolean|null $exclusiveMinimum
     * @param boolean|null $exclusiveMaximum
     * @param integer|null $multipleOf
     * @throws \Exception
     */
    public function __construct(
        int $minimum = null,
        int $maximum = null,
        bool $exclusiveMinimum = null,
        bool $exclusiveMaximum = null,
        int $multipleOf = 1
    ) {
        if ($minimum !== null && !is_int($minimum)) {
            throw new \Exception('Minimum must be integer');
        }

        if ($maximum !== null && !is_int($maximum)) {
            throw new \Exception('Maximum must be integer');
        }

        if (!is_int($multipleOf) || $multipleOf < 0) {
            throw new \Exception('multipleOf must be a positive integer');
        }

        parent::__construct(
            $minimum,
            $maximum,
            $exclusiveMinimum,
            $exclusiveMaximum,
            $multipleOf
        );
    }

    protected function cast($value)
    {
        return (int) $value;
    }
}
