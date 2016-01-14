<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis.
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

use Phramework\Exceptions\IncorrectParametersException;
use Phramework\Validate\ValidateResult;

/**
 * Boolean validator.
 *
 * @property bool default
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 *
 * @since 0.0.0
 */
class BooleanValidator extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type.
     *
     * @var string
     */
    protected static $type = 'boolean';

    public function __construct($default = null)
    {
        parent::__construct();

        $this->default = $default;
    }

    /**
     * Validate value.
     *
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     *
     * @param mixed $value Value to validate
     *
     * @return ValidateResult
     *
     * @uses filter_var with filter FILTER_VALIDATE_BOOLEAN
     *
     * @see https://secure.php.net/manual/en/filter.filters.validate.php
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);

        $filterValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, [
            'flags' => FILTER_NULL_ON_FAILURE,
        ]);

        if ($filterValue === null) {
            //error
            $return->errorObject = new IncorrectParametersException([
                [
                    'type'    => static::getType(),
                    'failure' => 'type',
                ],
            ]);
        } else {
            $return->errorObject = null;

            //Set status to success
            $return->status = true;
            //Type cast
            $return->value = $filterValue;
        }

        return $this->validateCommon($value, $return);
    }
}
