<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validators\LengthValidator;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Length Validator
 */
class LengthTest extends TestBaseClass
{
    /**
     * @testdox API length validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $configMinMax = [
            'length' => ['min' => 4, 'max' => 5]
        ];
        $configMin = [
            'length' => ['min' => 4]
        ];

        $validator = new LengthValidator();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configMinMax);
        $this->assertEquals(['min' => 4, 'max' => 5], $normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configMin);
        $this->assertEquals(['min' => 4], $normalisedConfig);
    }

    /**
     * @testdox API length validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueNull = null;
        $value = 'value';
        $valueTooLong = 'valuelong';
        $valueTooShort = 'val';
        $config = [
            'length' => ['min' => 4, 'max' => 5]
        ];
        $configMin = [
            'length' => ['min' => 4]
        ];
        $configMax = [
            'length' => ['max' => 5]
        ];
        $configFalse = [
            'length' => false
        ];
        $data = [
            'test' => $value
        ];
        $dataTooLong = [
            'test' => $valueTooLong
        ];
        $dataTooShort = [
            'test' => $valueTooShort
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new LengthValidator();
        $result = $validator->validate($key, $value, $config, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $value, $configMin, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $value, $configMax, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configMax, $dataNotExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueTooLong, $config, $dataTooLong);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueTooShort, $config, $dataTooShort);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueTooShort, $configMin, $dataTooShort);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueTooLong, $configMax, $dataTooLong);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on non matching length
     */
    public function testThrowsTransformerExceptionOnNonMatchingLength()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['length' => ['min' => 4, 'max' => 5]]
        ]);
        $transformer->transform([
            'first_name' => 'Tim'
        ]);
    }
}
