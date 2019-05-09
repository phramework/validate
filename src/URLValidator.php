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
use Phramework\Exceptions\IncorrectParameterException;

/**
 * URL validator
 * @uses \Phramework\Validate\String As base implementation's rules to
 * validate that the value is a number and then applies additional rules
 * @property integer $minLength Minimum number of its characters, default is 0
 * @property integer|null $maxLength Maximum number of its characters
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor13
 * *5.1.  Validation keywords for numeric instances (number and integer)*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class URLValidator extends \Phramework\Validate\StringValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'url';

    /**
     * URLValidator constructor.
     * @param int      $minLength
     * @param int|null $maxLength
     */
    public function __construct(
        int $minLength = 0,
        int $maxLength = null
    ) {
        parent::__construct(
            $minLength,
            $maxLength
        );
    }

    /**
     * Validate value
     * @see \Phramework\Validate\Result for Result object
     * @see https://secure.php.net/manual/en/filter.filters.validate.php
     * @param  mixed $value Value to validate
     * @return Result
     */
    public function validate($value)
    {
        //Use string's validator
        $return = parent::validate($value);

        //Apply additional rules
        if ($return->status == true) {
            if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                //error
                $return->status = false;

                $return->exception = new IncorrectParameterException(
                    'format',
                    null,
                    $this->source
                );
            }
        }

        return $return;
    }
}
