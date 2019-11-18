<?php

namespace Phramework\Validate\Formats;

use Phramework\Validate\IFormatValidator;
use Phramework\Validate\ValidateResult;

/**
 * Validates that a property of type 'string' and format
 * 'date-time' is an RFC3339 formatted string
 *
 * @author Alex Kalliontzis <alkallio@gmail.com>
 * @since 0.11.0
 */
class StringFormatValidatorValidator implements IFormatValidator
{
    protected static $type = 'string';

    public function validateFormat(
        string $data,
        string $format,
        \stdClass $formatProperties = null
    ): ValidateResult {
        $validateResult = new ValidateResult(
            $data,
            true
        );

        switch ($format) {
            case 'date-time':
                //Todo Create a TDO for formatProperties
                $validateResult = (new DateTime())
                    ->validateFormat(
                        $data,
                        self::$type,
                        $formatProperties
                    );

                break;
        }

        return $validateResult;
    }
}
