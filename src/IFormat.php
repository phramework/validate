<?php

namespace Phramework\Validate;

interface IFormat
{
    public function validateFormat(
        string $data,
        string $format,
        \stdClass $formatProperties = null
    ): IFormat;
}
