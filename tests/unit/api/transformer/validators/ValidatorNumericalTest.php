<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorNumerical;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Numerical Validator
 */
class ValidatorNumericalTest extends TestBaseClass
{
    /**
     * @testdox API numerical validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $configTrue = [
            'numerical' => true
        ];
        $configSimple = [
            'numerical'
        ];
        $configMin = [
            'numerical' => ['min' => 1]
        ];
        $configMax = [
            'numerical' => ['max' => 50]
        ];
        $configMinMax = [
            'numerical' => ['min' => 1, 'max' => 50]
        ];

        $validator = new ValidatorNumerical();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configTrue);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configSimple);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configMin);
        $this->assertEquals(['min' => 1], $normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configMax);
        $this->assertEquals(['max' => 50], $normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configMinMax);
        $this->assertEquals(['min' => 1, 'max' => 50], $normalisedConfig);
    }

    /**
     * @testdox API numerical validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueNull = null;
        $valueNumerical = '49';
        $valueTooHigh = '51';
        $valueTooLow = '0';
        $valueNotNumerical = '123D';
        $configTrue = [
            'numerical' => true
        ];
        $configMinMax = [
            'numerical' => ['min' => 1, 'max' => 50]
        ];
        $configFalse = [
            'numerical' => false
        ];
        $dataNumerical = [
            'test' => $valueNumerical
        ];
        $dataNotNumerical = [
            'test' => $valueNotNumerical
        ];
        $dataTooHigh = [
            'test' => $valueTooHigh
        ];
        $dataTooLow = [
            'test' => $valueTooLow
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new ValidatorNumerical();
        $result = $validator->validate($key, $valueNumerical, $configTrue, $dataNumerical);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNumerical, $configMinMax, $dataNumerical);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNumerical, $configFalse, $dataNumerical);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configTrue, $dataNotExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNotNumerical, $configTrue, $dataNotNumerical);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueTooHigh, $configMinMax, $dataTooHigh);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueTooLow, $configMinMax, $dataTooLow);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on invalid numerical
     */
    public function testThrowsTransformerExceptionOnNonMatchingNumerical()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'number' => ['numerical' => ['min' => 1, 'max' => 50]]
        ]);
        $transformer->transform([
            'number' => '52'
        ]);
    }
}
