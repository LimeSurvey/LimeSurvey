<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorNull;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Null Validator
 */
class ValidatorNullTest extends TestBaseClass
{
    /**
     * @testdox API null validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $config = [
            'null' => false
        ];

        $validator = new ValidatorNull();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($config);
        $this->assertFalse($normalisedConfig);
    }

    /**
     * @testdox API null validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueNull = null;
        $valueNotNull = 'value';
        $configTrue = [
            'null' => true
        ];
        $configFalse = [
            'null' => false
        ];
        $dataNotNull = [
            'test' => $valueNotNull
        ];
        $dataNull = [
            'test' => $valueNull
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new ValidatorNull();
        $result = $validator->validate($key, $valueNotNull, $configFalse, $dataNotNull);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configTrue, $dataNull);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configFalse, $dataNull);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueNull, $configFalse, $dataNotExists);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on non null value
     */
    public function testThrowsTransformerExceptionOnNullValue()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['null' => false]
        ]);
        $transformer->transform([
            'first_name' => null
        ]);
    }
}
