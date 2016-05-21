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
 * Array validator
 * @property BaseValidator|BaseValidator[]|null $items If it is an object,
 * this object MUST be a valid JSON Schema. If it is an array, items of this
 * array MUST be objects, and each of these objects MUST be a valid JSON Schema.
 * @property integer $minItems Minimum number of items
 * @property integer $maxItems Maximum number of items
 * @property boolean $uniqueItems If true, only unique array items are allowed
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor36
 * Validation keywords for arrays
 * @since 0.0.0
 */
class ArrayValidator extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'array';

    protected static $typeAttributes = [
        'minItems',
        'maxItems',
        'items',
        'uniqueItems',
        'additionalItems'
    ];

    /**
     * @param integer                                $minItems
     *     *[Optional]* Default is 0
     * @param integer|null                           $maxItems
     *     *[Optional]*
     * @param BaseValidator|null     $items
     *     *[Optional]* Default is null
     * @param Boolean                                $uniqueItems
     *     *[Optional]*
     * @throws \Exception
     */
    public function __construct(
        int $minItems = 0,
        int $maxItems = null,
        $items = null,
        bool $uniqueItems = false
    ) {
        parent::__construct();

        if (is_array($items)) {
            throw new \Exception(
                'Array for attribute "items" are not supported yet'
            );
        }

        if ($minItems < 0) {
            throw new \Exception('minItems must be positive integer');
        }

        if ($maxItems !== null &&  $maxItems < $minItems) {
            throw new \Exception('maxItems must be positive integer');
        }

        if ($items !== null && !is_subclass_of(
            $items,
            BaseValidator::class,
            true
        )) {
            throw new \Exception(sprintf(
                'Property "items" MUST extend "%s"',
                BaseValidator::class
            ));
        }

        $this->minItems = $minItems;
        $this->maxItems = $maxItems;
        $this->items = $items;
        $this->uniqueItems = $uniqueItems;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return Result
     * @todo incomplete
     */
    public function validate($value)
    {
        $return = new Result($value, false);

        if (!is_array($value)) {
            $return->exception = 'properties validation';
            //error
            $return->exception = new IncorrectParameterException(
                'type',
                null,
                $this->source
            );
            return $return;
        } else {
            $propertiesCount = count($value);

            if ($propertiesCount < $this->minItems) {
                //error
                $return->exception = new IncorrectParameterException(
                    'minItems',
                    null,
                    $this->source
                );
                return $return;
            } elseif ($this->maxItems !== null
                && $propertiesCount > $this->maxItems
            ) {
                $return->exception = new IncorrectParameterException(
                    'maxItems',
                    null,
                    $this->source
                );
                //error
                return $return;
            }
        }

        if ($this->items !== null) {
            $errorItems = [];
            //Currently we support only a single type
            foreach ($value as $k => $v) {
                $validateItems = $this->items->validate($v);

                if (!$validateItems->status) {
                    //$errorItems[$k] = $validateItems->exception->getParameters()[0];
                    $errorItems[] = $k;
                } else {
                    $value[$k] = $validateItems->value;
                }
            }

            if (!empty($errorItems)) {
                //@todo or add all into a IncorrectParametersException
                //'items' => [
                // $errorItems
                //        ]
                // 
                $return->exception = new IncorrectParameterException(
                    'items',
                    null,
                    $this->source
                );
                return $return;
            }
        }

        //Check if contains duplicate items
        if ($this->uniqueItems && count($value) !== count(array_unique($value))) {
            $return->exception = new IncorrectParameterException(
                'uniqueItems',
                null,
                $this->source
            );
            return $return;
        }

        //Success
        $return->exception = null;
        $return->status = true;

        //type casted
        $return->value = $value;

        return $this->validateCommon($value, $return);
    }

    /**
     * @param  array $a
     * @param  array $b
     * @return boolean
     * @since 0.4.0
     */
    public static function equals($a, $b)
    {
        return (
            is_array($a)
            && is_array($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }
}
