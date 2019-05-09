<?php

declare(strict_types=1);

namespace Phramework\Validate;

/**
 * @author Spafaridis Xenofon <nohponex@gmail.com>
 * @author Nikolopoulos Konstantinos <kosnikolopoulos@gmail.com>
 */
class UUIDv4Validator extends StringValidator
{
    /**
     * Overwrite base class type
     * @var string
     */
    protected static $type = 'UUIDv4';

    public function __construct()
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

        parent::__construct(0, 36, $pattern);
    }
}
