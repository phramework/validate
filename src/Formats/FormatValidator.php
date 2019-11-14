<?php

namespace Phramework\Validate\Formats;

use Phramework\Validate\IFormat;

class FormatValidator implements IFormat
{
    protected $isValid = true;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }
    protected $failure = '';

    /**
     * @return string
     */
    public function getFailureReason()
    {
        return $this->failure;
    }

    public function validateFormat(
        string $data,
        string $format,
        \stdClass $formatProperties = null
    ): IFormat {
        switch ($format) {
            case "date-time":
                //Todo Create a TDO for formatProperties
                $dateTimeFormatValidator = (new DateTime())
                    ->validateFormat(
                        $data,
                        $formatProperties
                    );

                if (!$dateTimeFormatValidator->isValid()) {
                    $this->isValid = false;
                    $this->failure = $dateTimeFormatValidator->getFailure();
                    return $this;
                }
                break;
        }

        return $this;
    }
}
