<?php

namespace Phramework\Validate\Formats;

use Phramework\Exceptions\IncorrectParametersException;
use Phramework\Validate\ValidateResult;

/**
 * Class DateTime
 *
 * @author Alex Kalliontzis <alkallio@gmail.com>
 * @internal Helper class for internal use
 * @since 0.11.0
 */
class DateTime
{
    const REGEX = '/^(?<year>\d{4})-(?<month>0[1-9]|1[0-2])-(?<day>0[1-9]|[12][0-9]|3[01])' .
        'T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)' .
        '(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))$/i';

    public function validateFormat(
        string $data,
        string $type,
        \stdClass $formatProperties
    ): ValidateResult {
        $matches = [];
        if (!preg_match(self::REGEX, $data, $matches)) {
            return new ValidateResult(
                $data,
                false,
                new IncorrectParametersException([
                    [
                        'type' => $type,
                        'failure' => 'date-time',
                    ],
                ])
            );
        }

        //Check if date is valid. This is needed because DateTime
        //will parse an invalid date like 2019-02-31
        if (!checkdate((int)$matches ['month'], (int) $matches['day'], (int) $matches['year'])) {
            return new ValidateResult(
                $data,
                false,
                new IncorrectParametersException([
                    [
                        'type' => $type,
                        'failure' => 'date-time',
                    ],
                ])
            );
        }

        try {
            $date = (new \DateTime($data))->getTimestamp();
        } catch (\Exception $e) {
            return new ValidateResult(
                $data,
                false,
                new IncorrectParametersException(
                    [
                        'type' => $type,
                        'failure' => 'date-time',
                    ]
                )
            );
        }

        if ($formatProperties->formatMinimum !== null) {
            $formatMinimum = (new \DateTime(
                $formatProperties->formatMinimum
            ))->getTimestamp();

            if ($date < $formatMinimum) {
                return new ValidateResult(
                    $data,
                    false,
                    new IncorrectParametersException(
                        [
                            'type' => $type,
                            'failure' => 'formatMinimum',
                        ]
                    )
                );
            }
        }

        if ($formatProperties->formatMaximum !== null) {
            $formatMaximum = (new \DateTime(
                $formatProperties->formatMaximum
            ))->getTimestamp();

            if ($date > $formatMaximum) {
                return new ValidateResult(
                    $data,
                    false,
                    new IncorrectParametersException(
                        [
                            'type' => $type,
                            'failure' => 'formatMaximum',
                        ]
                    )
                );
            }
        }

        return new ValidateResult(
            $data,
            true
        );
    }
}
