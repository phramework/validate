<?php

namespace Phramework\Validate;

/**
 * Interface IFormatValidator
 *
 * @author Alex Kalliontzis <alkallio@gmail.com>
 * @since 0.11.0
 */
interface IFormatValidator
{
    public function validateFormat(
        string $data,
        string $format,
        \stdClass $formatProperties = null
    ): ValidateResult;
}
