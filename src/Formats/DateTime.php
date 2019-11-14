<?php

namespace Phramework\Validate\Formats;

class DateTime
{
    const REGEX = '/^(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])' .
        'T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)' .
        '(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))$/i';

    protected $isValid = true;
    protected $failure = '';

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return string
     */
    public function getFailure()
    {
        return $this->failure;
    }

    public function validateFormat(
        string $data,
        \stdClass $formatProperties
    ): self {
        if (!preg_match(self::REGEX, $data)) {
            $this->isValid = false;
            $this->failure = 'pattern';
            return $this;
        }

        $date = (new \DateTime($data))->getTimestamp();

        if ($formatProperties->formatMinimum !== null) {
            $formatMinimum = (new \DateTime(
                $formatProperties->formatMinimum
            ))->getTimestamp();

            if ($date < $formatMinimum) {
                $this->isValid = false;
                $this->failure = 'Lower than minimum';
                return $this;
            }
        }

        if ($formatProperties->formatMaximum !== null) {
            $formatMaximum = (new \DateTime(
                $formatProperties->formatMaximum
            ))->getTimestamp();

            if ($date > $formatMaximum) {
                $this->isValid = false;
                $this->failure = 'Higher than maximum';
                return $this;
            }
        }

        return $this;
    }
}
