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
use Phramework\Exceptions\MissingParametersException;

/**
 * Provides various methods for validating data for varius datatypes.
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 *
 * @deprecated since 0.0.0
 */
class Validate
{
    /**
     * Required field.
     */
    const REQUIRED = 'required';

    /**
     * Text type.
     */
    const TYPE_TEXT = 'text';

    /**
     * Alias of Text type.
     */
    const TYPE_STRING = 'text';

    /**
     * Multiline text type.
     */
    const TYPE_TEXTAREA = 'textarea';

    /**
     * Signed integer type.
     */
    const TYPE_INT = 'int';

    /**
     * Unsigned integer type.
     */
    const TYPE_UINT = 'uint';

    /**
     * Floating point number type.
     */
    const TYPE_FLOAT = 'float';

    /**
     * Double presision floating point number type.
     */
    const TYPE_DOUBLE = 'double';

    /**
     * boolean type.
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * Color type.
     */
    const TYPE_COLOR = 'color';
    const TYPE_USERNAME = 'username';
    const TYPE_EMAIL = 'email';
    const TYPE_PASSWORD = 'password';
    const TYPE_TOKEN = 'token';
    const TYPE_URL = 'url';
    const TYPE_PERMALINK = 'permalink';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_REGEXP = 'regexp';

    /**
     * Unix timestamp (unsigned integer).
     */
    const TYPE_UNIX_TIMESTAMP = 'unix_timestamp';
    /**
     * This type allows only specific values.
     *
     * when used in model an array named values MUST be set for example `'values' => [1, 2, 'abc']`
     */
    const TYPE_ENUM = 'enum';
    const TYPE_JSON = 'json';
    const TYPE_JSON_ARRAY = 'json_array';
    const TYPE_ARRAY = 'array';

    /**
     * Comma separated array.
     *
     * When used in a Validate::model it splits the values ,
     * validates the subtype and returns as array
     *
     * @example aaa,bb,cc,1,2 Example parameter data
     *
     * @property string subtype [optional] Defines the subtype, default text
     */
    const TYPE_ARRAY_CSV = 'array_csv';

    /**
     * Regular expression of resource ID.
     */
    const REGEXP_RESOURCE_ID = '/^d+$/';  //'/^[A-Za-z0-9_]{3,128}$/' );

    /**
     * Regular expresion of username.
     */
    const REGEXP_USERNAME = '/^[A-Za-z0-9_\.]{3,64}$/';
    /**
     * Regular expresion of a token.
     */
    const REGEXP_TOKEN = '/^[A-Za-z0-9_]{3,48}$/';
    /**
     * Regular expresion of a permalink.
     */
    const REGEXP_PERMALINK = '/^[A-Za-z0-9_]{3,32}$/';

    const COLOR_HEX = 'hex';

    /**
     * Custom data types validators.
     *
     * This array holds the defined custom types
     *
     * @var array
     */
    private static $custom_types = [];

    /**
     * Register a custom data type.
     *
     * It can be used to validate models
     *
     * @param string   $type
     * @param function $callback
     *
     * @throws \Exception
     */
    public static function registerCustomType($type, $callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception(__('callback_is_not_function_exception'));
        }

        self::$custom_types[$type] = ['callback' => $callback];
    }

    /**
     * Validate a custom data type.
     *
     * This method uses previous custom-defined datatype to validate it's data.
     *
     * @param string $type       Custom type's name
     * @param mixed  $value      Value to test
     * @param string $field_name [optional] field's name
     * @param array  $model      [optional]
     *
     * @throws \Exception          type_not_found
     * @throws IncorrectParameters if validation fails
     */
    public static function validateCustomType($type, $value, $field_name, $model = [])
    {
        if (!isset(self::$custom_types[$type])) {
            throw new \Exception('type_not_found');
        }
        $callback = self::$custom_types[$type]['callback'];

        $output = false;

        if ($callback($value, $model, $output) === false) {
            //Incorrect
            throw new IncorrectParametersException([$field_name]);
        } else {
            //update output
            return $output;
        }
    }

    /**
     * Define available operators.
     */
    /*public static $operators = [
        OPERATOR_EMPTY, OPERATOR_EQUAL, OPERATOR_GREATER, OPERATOR_GREATER_EQUAL,
        OPERATOR_ISSET, OPERATOR_LESS, OPERATOR_LESS_EQUAL, OPERATOR_NOT_EMPTY,
        OPERATOR_NOT_EQUAL, OPERATOR_NOT_ISSET, OPERATOR_ISNULL, OPERATOR_NOT_ISNULL,
        OPERATOR_IN, OPERATOR_NOT_IN, OPERATOR_LIKE, OPERATOR_NOT_LIKE];*/

    /**
     * Validate a model.
     *
     * This method accepts a request model, and validates.
     * The values of $parameters array might be changed due to type casting.
     *
     * @param array $parameters Request parameters
     * @param array $model      Model used for the validation
     *
     * @throws \Exception
     * @throws IncorrectParameters If any field is incorrect
     * @throws MissingParameters   If any required field is missing
     *
     * @return bool
     */
    public static function model(&$parameters, $model)
    {
        //holds incorrect fields
        $incorrect = [];
        //holds missing fields
        $missing = [];

        foreach ($model as $key => $value) {
            if (!isset($parameters[$key])) {
                if (is_array($value) && (
                    (isset($value[self::REQUIRED]) && $value[self::REQUIRED]) ||
                    in_array(self::REQUIRED, $value, true) === true)) {
                    array_push($missing, $key);
                } elseif (is_array($value) && array_key_exists('default', $value)) {
                    $parameters[$key] = $value['default'];
                }
            } else {
                if (!is_array($value)) {
                    $parameters[$key] = strip_tags(self::filter_STRING($parameters[$key]));
                    continue;
                }
                $temporary_exception_description = ['type' => $value['type']];
                switch ($value['type']) {
                    case self::TYPE_INT:
                        if (filter_var($parameters[$key], FILTER_VALIDATE_INT) === false) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            if (isset($value['max'])
                                && $value['max'] !== null
                                && $parameters[$key] > $value['max']
                            ) {
                                $temporary_exception_description['failure'] = 'max';
                                $temporary_exception_description['max'] = $value['max'];
                                $incorrect[$key] = $temporary_exception_description;
                            } elseif (isset($value['min'])
                                && $value['min'] !== null
                                && $parameters[$key] < $value['min']
                            ) {
                                $temporary_exception_description['failure'] = 'min';
                                $temporary_exception_description['min'] = $value['min'];
                                $incorrect[$key] = $temporary_exception_description;
                            }

                            $parameters[$key] = intval($parameters[$key]);
                        }
                        break;
                    case self::TYPE_UINT:
                    case self::TYPE_UNIX_TIMESTAMP:
                        if (!isset($value['max'])) {
                            $value['min'] = 0;
                        }

                        if (filter_var($parameters[$key], FILTER_VALIDATE_INT) === false) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            if (isset($value['max'])
                                && $value['max'] !== null
                                && $parameters[$key] > $value['max']
                            ) {
                                $temporary_exception_description['failure'] = 'max';
                                $temporary_exception_description['max'] = $value['max'];
                                $incorrect[$key] = $temporary_exception_description;
                            } elseif (isset($value['min'])
                                && $value['min'] !== null
                                && $parameters[$key] < $value['min']
                            ) {
                                $temporary_exception_description['failure'] = 'min';
                                $temporary_exception_description['min'] = $value['min'];
                                $incorrect[$key] = $temporary_exception_description;
                            }

                            $parameters[$key] = intval($parameters[$key]);
                        }

                        break;
                    case self::TYPE_BOOLEAN:
                        //try to filter as boolean
                        $parameters[$key] = (boolean) ($parameters[$key]);
                        break;
                    case self::TYPE_DOUBLE:
                        //Replace comma with dot
                        $parameters[$key] = str_replace(',', '.', $parameters[$key]);

                        if (filter_var($parameters[$key], FILTER_VALIDATE_FLOAT) === false) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            if (isset($value['max'])
                                && $value['max'] !== null
                                && $parameters[$key] > $value['max']
                            ) {
                                $temporary_exception_description['failure'] = 'max';
                                $temporary_exception_description['max'] = $value['max'];
                                $incorrect[$key] = $temporary_exception_description;
                            } elseif (isset($value['min'])
                                && $value['min'] !== null
                                && $parameters[$key] < $value['min']
                            ) {
                                $temporary_exception_description['failure'] = 'min';
                                $temporary_exception_description['min'] = $value['min'];
                                $incorrect[$key] = $temporary_exception_description;
                            }

                            $parameters[$key] = floatval($parameters[$key]);
                        }
                        break;
                    case self::TYPE_FLOAT:
                        //Replace comma with dot
                        $parameters[$key] = str_replace(',', '.', $parameters[$key]);

                        if (filter_var(
                            $parameters[$key],
                            FILTER_VALIDATE_FLOAT,
                            ['options' => ['decimal' => '.']]
                        ) === false
                        ) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            if (isset($value['max'])
                                && $value['max'] !== null
                                && $parameters[$key] > $value['max']
                            ) {
                                $temporary_exception_description['failure'] = 'max';
                                $temporary_exception_description['max'] = $value['max'];
                                $incorrect[$key] = $temporary_exception_description;
                            } elseif (isset($value['min'])
                                && $value['min'] !== null
                                && $parameters[$key] < $value['min']
                            ) {
                                $temporary_exception_description['failure'] = 'min';
                                $temporary_exception_description['min'] = $value['min'];
                                $incorrect[$key] = $temporary_exception_description;
                            }

                            $parameters[$key] = floatval($parameters[$key]);
                        }
                        break;
                    case self::TYPE_USERNAME:
                        if (!preg_match(self::REGEXP_USERNAME, $parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_PERMALINK:
                        if (!preg_match(self::REGEXP_PERMALINK, $parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_TOKEN:
                        if (!preg_match(self::REGEXP_TOKEN, $parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_COLOR:
                        //@todo check (color_type) subtype
                        if (!preg_match('/^#[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8}$/', $parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_EMAIL:
                        if (empty($parameters[$key])
                            || filter_var($parameters[$key], FILTER_VALIDATE_EMAIL) === false
                        ) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            if (isset($value['max'])
                                && $value['max'] !== null
                                && mb_strlen($parameters[$key]) > $value['max']
                            ) {
                                $temporary_exception_description['failure'] = 'max';
                                $temporary_exception_description['max'] = $value['max'];
                                $incorrect[$key] = $temporary_exception_description;
                            }
                            if (isset($value['min'])
                                && $value['min'] !== null
                                && mb_strlen($parameters[$key]) < $value['min']
                            ) {
                                $temporary_exception_description['failure'] = 'min';
                                $temporary_exception_description['min'] = $value['min'];
                                $incorrect[$key] = $temporary_exception_description;
                            }
                        }
                        break;
                    case self::TYPE_URL:
                        if (filter_var($parameters[$key], FILTER_VALIDATE_URL) === false) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_DATE:
                    case self::TYPE_DATETIME:
                        if (!self::sqlDate($parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_REGEXP:
                        if (!isset($value['regexp'])) {
                            throw new \Exception(__('regexp_not_set_exception'));
                        }
                        if (!preg_match($value['regexp'], $parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_PASSWORD:
                        if (isset($value['max']) && $value['max'] !== null) {
                            if (mb_strlen($parameters[$key]) > $value['max']) {
                                $temporary_exception_description['failure'] = 'max';
                                $temporary_exception_description['max'] = $value['max'];
                                $incorrect[$key] = $temporary_exception_description;
                            }
                        }
                        if (isset($value['min']) && $value['min'] !== null) {
                            if (mb_strlen($parameters[$key]) < $value['min']) {
                                $temporary_exception_description['failure'] = 'min';
                                $temporary_exception_description['min'] = $value['min'];
                                $incorrect[$key] = $temporary_exception_description;
                            }
                        }
                        break;
                    case self::TYPE_ENUM:
                        if (!isset($value['values'])) {
                            //Internal error ! //TODO @security
                            throw new \Exception('Values not set');
                        }
                        if (!in_array($parameters[$key], $value['values'])) {
                            $temporary_exception_description['failure'] = 'not_allowed';
                            $temporary_exception_description['allowed'] = $value['values'];
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_JSON_ARRAY:
                        $temp = [];

                        //Force to array when is not []
                        if (!$parameters[$key]) {
                            $parameters[$key] = [];
                        }
                        foreach ($parameters[$key] as $t) {
                            $ob = json_decode($t, false);
                            if ($ob === null) {
                                $incorrect[$key] = $temporary_exception_description;
                            } else {
                                //Overwrite json
                                $temp[] = $ob;
                            }
                        }
                        $parameters[$key] = $temp;
                        break;
                    case self::TYPE_JSON:
                        $ob = json_decode($parameters[$key], false);
                        if ($ob === null) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            //Overwrite json
                            $parameters[$key] = $ob;
                        }
                        break;
                    case self::TYPE_ARRAY:
                        //Get single value
                        if (!is_array($parameters[$key])) {
                            $parameters[$key] = [$parameters[$key]];
                        }

                        if (isset($value['max'])
                            && $value['max'] !== null
                            && count($parameters[$key]) > $value['max']
                        ) {
                            $temporary_exception_description['failure'] = 'max';
                            $temporary_exception_description['max'] = $value['max'];
                            $incorrect[$key] = $temporary_exception_description;
                        }

                        if (isset($value['min'])
                            && $value['min'] !== null
                            && count($parameters[$key]) < $value['min']
                        ) {
                            $temporary_exception_description['failure'] = 'min';
                            $temporary_exception_description['min'] = $value['min'];
                            $incorrect[$key] = $temporary_exception_description;
                        }
                        break;
                    case self::TYPE_ARRAY_CSV:
                        if (!is_string($parameters[$key])) {
                            $incorrect[$key] = $temporary_exception_description;
                        } else {
                            $values = mbsplit(',', $parameters[$key]);

                            $subtype = (
                                isset($value['subtype'])
                                ? $value['subtype']
                                : self::TYPE_TEXT
                            );

                            //Validate every record of this subtype
                            foreach ($values as &$v) {
                                //Create temporary model
                                $m = [$key => $v];

                                //Validate this model
                                self::model(
                                    $m,
                                    [$key => ['type' => $subtype]]
                                );

                                //Overwrite $v
                                $v = $m[$key];
                            }
                            $parameters[$key] = $values;
                        }
                        break;
                    case self::TYPE_TEXT:
                    case self::TYPE_TEXTAREA:
                    default:
                        //Check if is custom_type
                        if (isset(self::$custom_types[$value['type']])) {
                            $callback = self::$custom_types[$value['type']]['callback'];

                            $output;

                            if ($callback($parameters[$key], $value, $output) === false) {
                                //Incorrect
                                $incorrect[$key] = $temporary_exception_description;
                            } else {
                                //update output
                                $parameters[$key] = $output;
                            }
                        } else {
                            if (isset($value['max']) && $value['max'] !== null) {
                                if (mb_strlen($parameters[$key]) > $value['max']) {
                                    $temporary_exception_description['failure'] = 'max';
                                    $temporary_exception_description['max'] = $value['max'];
                                    $incorrect[$key] = $temporary_exception_description;
                                }
                            }
                            if (isset($value['min']) && $value['min'] !== null) {
                                if (mb_strlen($parameters[$key]) < $value['min']) {
                                    $temporary_exception_description['failure'] = 'min';
                                    $temporary_exception_description['min'] = $value['min'];
                                    $incorrect[$key] = $temporary_exception_description;
                                }
                            }
                            //Ignore sting filtering only if raw flag is set
                            if (!in_array('raw', $value)) {
                                $parameters[$key] = strip_tags(
                                    filter_var($parameters[$key], FILTER_SANITIZE_STRING)
                                );
                            }
                        }
                }
            }
        }
        if ($incorrect) {
            throw new IncorrectParametersException($incorrect);
        } elseif ($missing) {
            throw new MissingParametersException($missing);
        }

        return true;
    }

    /**
     * Check if callback is valid.
     *
     * @link http://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/ source
     *
     * @param string $subject
     *
     * @return bool
     */
    public function isValidJsonpCallback($subject)
    {
        $identifier_syntax
          = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

        $reserved_words = ['break', 'do', 'instanceof', 'typeof', 'case',
          'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
          'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
          'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
          'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
          'private', 'public', 'yield', 'interface', 'package', 'protected',
          'static', 'null', 'true', 'false', ];

        return preg_match($identifier_syntax, $subject)
            && !in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
    }

    /**
     * Validate a signed integer.
     *
     * @param string|int $input      Input value
     * @param int|null   $min        Minimum value. [optional] Default is NULL, if NULL then the minum value is skipped
     * @param int|null   $max        Maximum value. [optional] Default is NULL, if NULL then the maximum value is skipped
     * @param string     $field_name [optional] Field's name, used in IncorrectParametersException. Optional default is int
     *
     * @throws IncorrectParameters If valu type is not correct.
     *
     * @return int Returns the value of the input value as int
     */
    public static function int($input, $min = null, $max = null, $field_name = 'int')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_INT, self::REQUIRED],
        ];

        if ($min !== null) {
            $model[$field_name]['min'] = $min;
        }

        if ($max !== null) {
            $model[$field_name]['max'] = $max;
        }

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate an unsigned integer.
     *
     * @param string|int $input      Input value
     * @param int        $min        Minimum value. Optional default is 0.
     * @param int|null   $max        Maximum value. Optional default is NULL, if NULL then the maximum value is skipped
     * @param string     $field_name Field's name, used in IncorrectParametersException. Optional default is uint
     *
     * @throws IncorrectParameters If valu type is not correct.
     *
     * @return int Returns the value of the input value as int
     */
    public static function uint($input, $min = 0, $max = null, $field_name = 'uint')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_UINT, self::REQUIRED],
        ];

        $model[$field_name]['min'] = $min;

        if ($max !== null) {
            $model[$field_name]['max'] = $max;
        }

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate a floating point number.
     *
     * @param string|float|int $input
     * @param float|null       $min   Minimum value. Optional default is NULL, if NULL then the minum value is skipped
     * @param float|null Maximum value. Optional default is NULL, if NULL then the maximum value is skipped
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is number
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return float Returns the input value as float
     */
    public static function float($input, $min = null, $max = null, $field_name = 'float')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_FLOAT, self::REQUIRED],
        ];

        if ($min !== null) {
            $model[$field_name]['min'] = $min;
        }

        if ($max !== null) {
            $model[$field_name]['max'] = $max;
        }

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate a double presision floating point number.
     *
     * @param string|float|float|int $input
     * @param float|null             $min   Minimum value. Optional default is NULL, if NULL then the minum value is skipped
     * @param float|null Maximum value. Optional default is NULL, if NULL then the maximum value is skipped
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is number
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return float Returns the input value as double
     */
    public static function double($input, $min = null, $max = null, $field_name = 'double')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_DOUBLE, self::REQUIRED],
        ];

        if ($min !== null) {
            $model[$field_name]['min'] = $min;
        }

        if ($max !== null) {
            $model[$field_name]['max'] = $max;
        }

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate an email address.
     *
     * @param string $input
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is email
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string Return the email address
     */
    public static function email($input, $field_name = 'email')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_EMAIL, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate a url.
     *
     * @param string $input
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is url
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string Return the url
     */
    public static function url($input, $field_name = 'url')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_URL, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate a permalink id.
     *
     * @param string $input
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is permalink
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string Return the permalink
     */
    public static function permalink($input, $field_name = 'permalink')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_PERMALINK, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * @param string $input
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is token
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string Return the token
     */
    public static function token($input, $field_name = 'token')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_TOKEN, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Check if input value is in allowed values.
     *
     * @param string|int $input      Input array to check
     * @param array      $values     Array of strings or number, defines the allowed input values
     * @param string     $field_name [optional] Field's name, used in IncorrectParametersException.
     *                               Optional default is enum
     *
     * @throws IncorrectParameters If valu type is not correct.
     *
     * @return returns the value of the input value
     */
    public static function enum($input, $values, $field_name = 'enum')
    {

        //Check if array was given for $values value
        if (!is_array($values)) {
            throw new \Exception('Array is expected as values');
        }

        //Define trivial model
        $model = [
            $field_name => [
                'type' => self::TYPE_ENUM, 'values' => $values, self::REQUIRED,
            ],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate SQL date, datetime.
     *
     * @param string $date       Input date
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is date
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string
     */
    public static function sqlDate($date, $field_name = 'date')
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $date, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return $date;
            }
        }
        throw new IncorrectParametersException([$field_name]);
    }

    /**
     * Validate color.
     *
     * @param type   $input      Input color
     * @param string $type       Color value type. Optional, default is hex
     * @param string $field_name [optional] Field's name, used in IncorrectParametersException.
     *                           Optional default is color
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string
     *
     * @todo Implement additional types
     */
    public static function color($input, $type = self::COLOR_HEX, $field_name = 'color')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_COLOR, 'color_type' => $type, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate an operator.
     *
     * @param string $operator
     * @param string $field_name [optional]
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string
     */
    public static function operator($operator, $field_name = 'operator')
    {
        if (!in_array($operator, self::$operators)) {
            throw new IncorrectParametersException([$field_name]);
        }

        return $operator;
    }

    /**
     * Validate a regexp.
     *
     * @param string input
     * @param string regexp
     * @param string $field_name [optional]
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return string
     */
    public static function regexp($input, $regexp, $field_name = 'regexp')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_REGEXP, 'regexp' => $regexp, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }

    /**
     * Validate a regexp.
     *
     * @param mixed input
     * @param string $field_name [optional]
     *
     * @throws IncorrectParameters If value type is not correct.
     *
     * @return bool
     */
    public static function boolean($input, $field_name = 'boolean')
    {
        //Define trivial model
        $model = [
            $field_name => ['type' => self::TYPE_BOOLEAN, self::REQUIRED],
        ];

        $parameters = [$field_name => $input];
        self::model($parameters, $model);

        return $parameters[$field_name];
    }
}
