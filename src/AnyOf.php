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
 * Validates successfully if it validates successfully against at least one schema defined in anyOf attribute
 * @property array anyOf
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @see http://json-schema.org/latest/json-schema-validation.html#anchor85
 * @since 0.4.0
 */
class AnyOf extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var null
     */
    protected static $type = null;

    protected static $typeAttributes = [
        'anyOf'
    ];

    /**
     * @param array $anyOf
     * @throws \Exception
     * @example
     * ```php
     * $validator = new AnyOf([
     *     new IntegerValidator(),
     *     new ArrayValidator(
     *         1,
     *         4,
     *         new IntegerValidator()
     *     )
     * ]);
     *
     * //Will parse successfully both
     *
     * $parsed = $validator->parse(8);
     * $parsed = $validator->parse([8, 10]);
     * ```
     */
    public function __construct(
        array $anyOf
    ) {
        parent::__construct();

        foreach ($anyOf as $validator) {
            if (!($validator instanceof \Phramework\Validate\BaseValidator)) {
                throw new \Exception(sprintf(
                    'Items of %s parameter MUST be instances of Phramework\Validate\BaseValidator',
                    $this->anyOfProperty
                ));
            }
        }

        $this->{$this->anyOfProperty} = $anyOf;
    }

    /**
     * When not null, a specific count of passed anyOf validations will be used in
     * validate method.
     * This internal parameter is useful for oneOf, and allOf classes
     * @var integer|null
     */
    protected $requiredCountOfAnyOf = null;

    /**
     * @var string
     */
    protected $anyOfProperty = 'anyOf';

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @uses $requiredCountOfAnyOf
     * @uses $anyOfProperty
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);
        //validator ->
        //return    ->
        $successValidated = [];

        foreach ($this->{$this->anyOfProperty} as $validator) {
            $validatorReturn = $validator->validate($value);

            if ($validatorReturn->status) {
                //push to successValidated list
                $successValidated[] = (object)[
                    'validator' => $validator,
                    'return'    => $validatorReturn
                ];
            }
        }

        if ((
                $this->requiredCountOfAnyOf === null
                && count($successValidated) > 0
            ) || (
                $this->requiredCountOfAnyOf !== null
                && count($successValidated) === $this->requiredCountOfAnyOf
            )
        ) {
            //Use first in list
            $return = $successValidated[0]->return;
            return $this->validateCommon($value, $return);
        }

        //error
        $return->exception = new IncorrectParametersException([
            [
                'type' => static::getType(),
                'failure' => $this->anyOfProperty
            ]
        ]);

        unset($successValidated);

        return $return;
    }
}
