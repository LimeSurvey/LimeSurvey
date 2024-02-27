<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorEmpty;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Empty Validator
 */
class ValidatorEmptyTest extends TestBaseClass
{
    /**
     * @testdox API empty validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $config = [
            'empty' => false
        ];

        $validator = new ValidatorEmpty();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($config);
        $this->assertFalse($normalisedConfig);
    }

    /**
     * @testdox API empty validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueEmpty1 = null;
        $valueEmpty2 = [];
        $valueEmpty3 = '';
        $valueNotEmpty = 'value';
        $configTrue = [
            'empty' => true
        ];
        $configFalse = [
            'empty' => false
        ];
        $dataNotEmpty = [
            'test' => $valueNotEmpty
        ];
        $dataEmpty1 = [
            'test' => $valueEmpty1
        ];
        $dataEmpty2 = [
            'test' => $valueEmpty2
        ];
        $dataEmpty3 = [
            'test' => $valueEmpty3
        ];
        $validator = new ValidatorEmpty();
        $result = $validator->validate($key, $valueNotEmpty, $configFalse, $dataNotEmpty);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueEmpty1, $configTrue, $dataEmpty1);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueEmpty1, $configFalse, $dataEmpty1);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueEmpty2, $configFalse, $dataEmpty2);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueEmpty3, $configFalse, $dataEmpty3);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on empty value
     */
    public function testThrowsTransformerExceptionOnEmptyValue()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['empty' => false]
        ]);
        $transformer->transform([
            'first_name' => null
        ]);
    }
}
