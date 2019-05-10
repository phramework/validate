<?php
/**
 * Copyright 2015-2019 Xenofon Spafaridis
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;
use Phramework\Exceptions\IncorrectParameterException;
use Phramework\Exceptions\Source\ISource;
use Phramework\Exceptions\Source\Pointer;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class BooleanValidatorTest extends TestCase
{
    /**
     * @var BooleanValidator
     */
    protected $object;

    /**
     * @var ISource
     */
    protected $source;
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->source = new Pointer('/data/attributes/name');

        $this->object = new BooleanValidator();

        $this->object->setSource(
            $this->source
        );
    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            [1, true],
            ['1', true],
            [true, true],
            ['true', true],
            ['TRUE', true],
            ['yes', true],
            ['on', true],
            [0, false],
            ['0', false],
            [false, false],
            ['false', false],
            ['FALSE', false],
            ['no', false],
            ['off', false]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['100'],
            ['01'],
            [10],
            [-1],
            [124],
            ['τρθε'],
            ['positive'],
            ['negative']
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertIsBool($return->value);
        $this->assertEquals($expected, $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertEquals(false, $return->status);

        $this->assertInstanceOf(
            IncorrectParameterException::class,
            $return->exception
        );

        $this->assertSame('type', $return->exception->getFailure());

        //Assert that source has been passed to exception
        $this->assertSame($this->source, $return->exception->getSource());
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommonEnum()
    {
        $validator = (new BooleanValidator());

        $validator->enum = [true];

        $return = $validator->validate(true);

        $this->assertTrue(
            $return->status,
            'Expect true since true is in enum array'
        );
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommonEnumFailure()
    {
        $validator = (new BooleanValidator())->setSource($this->source);

        $validator->enum = [true];
        
        $return = $validator->validate(false);

        $this->assertFalse(
            $return->status,
            'Expect false since false is not in enum array'
        );

        $this->assertInstanceOf(
            IncorrectParameterException::class,
            $return->exception
        );

        $this->assertSame('enum', $return->exception->getFailure());

        //Assert that source has been passed to exception
        $this->assertSame($this->source, $return->exception->getSource());
    }

    /**
     */
    public function testGetType()
    {
        $this->assertEquals('boolean', $this->object->getType());
    }
}
