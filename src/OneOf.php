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
 * Validates successfully if it validates successfully against exactly one
 * schema defined in oneOf attribute
 * @property array oneOf
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor88
 * @since 0.4.0
 */
class OneOf extends \Phramework\Validate\AnyOf
{
    /**
     * Overwrite base class type
     * @var null
     */
    protected static $type = null;

    protected static $typeAttributes = [
        'oneOf'
    ];

    /**
     * @var string
     */
    protected $anyOfProperty = 'oneOf';

    /**
     * @param BaseValidator[] $oneOf
     * @throws \Exception
     * @example
     * ```php
     * $validator = new OneOf(
     *     new IntegerValidator(0, 7),
     *     new NumberValidator(5, 10)
     * );
     *
     * //Will parse successfully both
     *
     * $parsed = $validator->parse(3);
     * $parsed = $validator->parse(7);
     *
     * //but NOT!
     * $parsed = $validator->parse(6);
     * ```
     */
    public function __construct(
        BaseValidator ...$oneOf
    ) {
        parent::__construct(...$oneOf);
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return Result
     * @uses $requiredCountOfAnyOf
     * @uses \Phramework\Validate\AnyOf::validate
     */
    public function validate($value)
    {
        $this->requiredCountOfAnyOf = 1;

        return parent::validate($value);
    }
}
