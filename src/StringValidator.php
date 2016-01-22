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

use \Phramework\Models\Filter;
use \Phramework\Exceptions\IncorrectParametersException;

/**
 * String validator
 * @property integer $minLength Minimum number of its characters
 * @property integer|null $maxLength Maximum number of its characters
 * @property string|null $pattern Regular expression pattern for validating
 * @property boolean $raw Keep raw value, don't sanitize value after validation
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 * @see ECMA 262 regular expression dialect for regular expression pattern
 */
class StringValidator extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'string';

    protected static $typeAttributes = [
        'minLength',
        'maxLength',
        'pattern',
        'raw' //non standard
    ];

    /**
     * @param integer       $minLength *[Optional]*
     *     Minimum number of its characters, default is 0
     * @param integer|null  $maxLength *[Optional]*
     *     Maximum number of its characters, default is null
     * @param string|null   $pattern   *[Optional]*
     *     Regular expression pattern for validating, default is null
     * @param boolean       $raw       *[Optional]*
     *     Keep raw value, don't sanitize value after validation, default is false
     * @throws \Exception
     */
    public function __construct(
        $minLength = 0,
        $maxLength = null,
        $pattern = null,
        $raw = false
    ) {
        parent::__construct();

        if (!is_int($minLength) || $minLength < 0) {
            throw new \Exception('minLength must be positive integer');
        }

        if ($maxLength !== null && (!is_int($maxLength) || $maxLength < $minLength)) {
            throw new \Exception('maxLength must be positive integer');
        }

        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
        $this->raw = $raw;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @uses https://secure.php.net/manual/en/function.is-string.php
     * @uses filter_var with FILTER_VALIDATE_REGEXP for pattern
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        if (!is_string($value)) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'type'
                ]
            ]);
        } elseif (mb_strlen($value) < $this->minLength) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'minLength'
                ]
            ]);
        } elseif ($this->maxLength !== null
            && mb_strlen($value) > $this->maxLength
        ) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'maxLength'
                ]
            ]);
        } elseif ($this->pattern !== null
            && filter_var(
                $value,
                FILTER_VALIDATE_REGEXP,
                [
                    'options' => ['regexp' => $this->pattern]
                ]
            ) === false
        ) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type' => static::getType(),
                    'failure' => 'pattern'
                ]
            ]);
        } else {
            $return->errorObject = null;
            //Set status to success
            $return->status = true;

            if ($this->raw) {
                //use raw
                $return->value = $value;
            } else {
                //or filter
                $return->value = strip_tags(
                    filter_var($value, FILTER_SANITIZE_STRING)
                );
            }
        }

        return $this->validateCommon($value, $return);
    }
}
