<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
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
class StringValidatorTest extends TestCase
{

    /**
     * @var String
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

        $this->object = new StringValidator(
            4,
            10,
            '/^[a-z][a-z0-9]{3,8}[0-9]$/'
        );

        $this->object->setSource(
            $this->source
        );
    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            ['abx34scd3', 'abx34scd3'],
            ['abcd0', 'abcd0'],
            ['a2cx2', 'a2cx2'],
        ];
    }

    public function validateFailureProvider()
    {
        //input, failure
        return [
            ['', 'minLength'],
            [new \stdClass(), 'type'],
            [['x', 'array'], 'type'],
            [1, 'type'],
            [2, 'type'],
            [-10, 'type'],
            ['az9', 'minLength'],
            ['abcc', 'pattern'],
            ['9abc4', 'pattern'],
            ['asssssssssssssssssssbc9', 'maxLength'],
        ];
    }

    /**
     */
    public function testConstruct()
    {
        $validator = new StringValidator();
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure()
    {
        $validator = new StringValidator(-1);
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructFailure2()
    {
        $validator = new StringValidator(3, 2);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
        $this->assertEquals($expected, $return->value);
        $this->assertTrue($return->status);
    }

    /**
     */
    public function testValidateSuccessRaw()
    {
        $this->object->raw = true;

        $return = $this->object->validate('abx34scd3');

        $this->assertInternalType('string', $return->value);
        $this->assertEquals('abx34scd3', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input, $failure)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            IncorrectParameterException::class,
            $return->exception
        );

        $this->assertSame($failure, $return->exception->getFailure());

        //Assert that source has been passed to exception
        $this->assertSame($this->source, $return->exception->getSource());
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommonEnum()
    {
        $validator = (new StringValidator(0, 10));

        $validator->enum = ['aa', 'bb'];

        $return = $validator->validate('aa');
        $this->assertTrue(
            $return->status,
            'Expect true since "aa" is in enum array'
        );
    }

    /**
     * Validate against common enum keyword
     */
    public function testValidateCommonEnumFailure()
    {
        $validator = (new StringValidator(0, 10))->setSource($this->source);

        $validator->enum = ['aa', 'bb'];

        $return = $validator->validate('cc');
        
        $this->assertFalse(
            $return->status,
            'Expect false since "cc" is not in enum array'
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
     * Validate against common not keyword
     */
    public function testValidateCommonNotFailure()
    {
        $validator = (new StringValidator())->setSource($this->source);

        $validator->not = (new StringValidator())->setEnum(['notthisvalue']);

        $return = $validator->validate('notthisvalue');

        $this->assertFalse($return->status);

        $this->assertInstanceOf(
            IncorrectParameterException::class,
            $return->exception
        );

        $this->assertSame('not', $return->exception->getFailure());

        //Assert that source has been passed to exception
        $this->assertSame($this->source, $return->exception->getSource());
    }

    /**
     */
    public function testGetType()
    {
        $this->assertEquals('string', $this->object->getType());
    }
}
