<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorRange;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Range Validator
 */
class ValidatorRangeTest extends TestBaseClass
{
    /**
     * @testdox API range validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $config = [
            'range' => ['A', 'B', 'C']
        ];

        $validator = new ValidatorRange();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($config);
        $this->assertEquals(['A', 'B', 'C'], $normalisedConfig);
    }

    /**
     * @testdox API range validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueNull = null;
        $valueInRange = 'A';
        $valueNotInRange = 'D';
        $configTrue = [
            'range' => ['A', 'B', 'C']
        ];
        $configFalse = [
            'range' => false
        ];
        $dataInRange = [
            'test' => 'A'
        ];
        $dataNotInRange = [
            'test' => 'D'
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new ValidatorRange();
        $result = $validator->validate($key, $valueInRange, $configTrue, $dataInRange);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueInRange, $configFalse, $dataInRange);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configTrue, $dataNotExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNotInRange, $configTrue, $dataNotInRange);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on non matching range
     */
    public function testThrowsTransformerExceptionOnNonMatchingRange()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['range' => ['A', 'B', 'C']]
        ]);
        $transformer->transform([
            'first_name' => 'D'
        ]);
    }
}
