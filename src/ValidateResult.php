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

/**
 * Helper class, contains the result of validator's validation.
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 *
 * @since 0.0.0
 */
class ValidateResult
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @var bool
     */
    public $status;

    /**
     * @var Exception|null
     */
    public $errorObject;

    /**
     * @param mixed          $value       [description]
     * @param bool           $status      [description]
     * @param Exception|null $errorObject [description]
     */
    public function __construct($value, $status = false, $errorObject = null)
    {
        $this->value = $value;
        $this->status = $status;
        $this->errorObject = $errorObject;
    }
}
