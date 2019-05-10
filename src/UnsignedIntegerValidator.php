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

/**
 * UnsignedInteger validator
 * @uses \Phramework\Validate\Integer As base implementation's rules to
 * validate that the value is a number and then applies additional rules
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
class UnsignedIntegerValidator extends \Phramework\Validate\IntegerValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'unsignedinteger';

    /**
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function __construct(
        int $minimum = 0,
        ?int $maximum = null,
        ?bool $exclusiveMinimum = false,
        ?bool $exclusiveMaximum = false,
        ?int $multipleOf = 1
    ) {
        if ($minimum < 0) {
            throw new \InvalidArgumentException('Minimum cannot be negative');
        }

        parent::__construct(
            $minimum,
            $maximum,
            $exclusiveMinimum,
            $exclusiveMaximum,
            $multipleOf
        );
    }
}
