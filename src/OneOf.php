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
 * @property array anyOf
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
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

    public function __construct(
        array $allOf
    ) {
        parent::__construct($allOf);
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @uses $requiredCountOfAnyOf
     * @uses \Phramework\Validate\AnyOf::validate
     */
    public function validate($value)
    {
        $this->requiredCountOfAnyOf = 1;

        return parent::validate($value);
    }
}
