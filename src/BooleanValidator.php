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

use Phramework\Exceptions\IncorrectParameterException;
use Phramework\Validate\Result\Result;

/**
 * Boolean validator
 * @property boolean default
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class BooleanValidator extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'boolean';

    /**
     * BooleanValidator constructor.
     * @param bool|null $default
     */
    public function __construct(bool $default = null)
    {
        parent::__construct();

        $this->default = $default;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\Result for Result object
     * @param  mixed $value Value to validate
     * @return Result
     * @uses filter_var with filter FILTER_VALIDATE_BOOLEAN
     * @see https://secure.php.net/manual/en/filter.filters.validate.php
     */
    public function validate($value)
    {
        $return = new Result($value, false);

        $filterValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, [
            'flags' => FILTER_NULL_ON_FAILURE
        ]);

        if ($filterValue === null) {
            //error
            $return->exception = new IncorrectParameterException(
                'type',
                null,
                $this->source
            );
        } else {
            $return->exception = null;

            //Set status to success
            $return->status = true;

            //Type cast
            $return->value = (bool) $filterValue;
        }

        return $this->validateCommon($value, $return);
    }
}
