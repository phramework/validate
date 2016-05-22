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
 * Null validator, used to expect null as a value
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
class NullValidator extends BaseValidator
{
    protected static $type = 'null';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        $result = new Result(
            $value,
            true
        );
            
        if ($value !== null) {
            $result->status = false;
            $result->exception = new IncorrectParameterException(
                'type',
                'Expected value "null"',
                $this->getSource()
            );
        }
        
        return $result;
    }
}
