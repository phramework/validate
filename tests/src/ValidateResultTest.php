<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

class ValidateResultTest extends TestCase
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \Phramework\Validate\ValidateResult::__construct
     */
    public function testConstruct()
    {
        $validator = new ValidateResult(true, true);
    }
}
