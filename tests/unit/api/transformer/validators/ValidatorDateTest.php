<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorDate;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Date Validator
 */
class ValidatorDateTest extends TestBaseClass
{
    /**
     * @testdox API date validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $config = [
            'date' => true
        ];
        $configSimple = [
            'date'
        ];

        $validator = new ValidatorDate();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($config);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configSimple);
        $this->assertTrue($normalisedConfig);
    }

    /**
     * @testdox API date validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueNull = null;
        $valueComplete = '2024-12-24T18:00:01.1234Z';
        $valueNoMili = '2024-12-24T18:00:01Z';
        $valueNoSec = '2024-12-24T18:00';
        $valueNoTime = '2024-12-24';
        $valueDoesntMatch = '24.12.2024 18:00:01';
        $configTrue = [
            'date' => true
        ];
        $configFalse = [
            'date' => false
        ];
        $dataComplete = [
            'test' => $valueComplete
        ];
        $dataNoMili = [
            'test' => $valueNoMili
        ];
        $dataNoSec = [
            'test' => $valueNoSec
        ];
        $dataNoTime = [
            'test' => $valueNoTime
        ];
        $dataDoesntMatch = [
            'test' => $valueDoesntMatch
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new ValidatorDate();
        $result = $validator->validate($key, $valueDoesntMatch, $configFalse, $dataDoesntMatch);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueComplete, $configTrue, $dataComplete);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNoMili, $configTrue, $dataNoMili);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNoSec, $configTrue, $dataNoSec);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNoTime, $configTrue, $dataNoTime);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configTrue, $dataNotExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueDoesntMatch, $configTrue, $dataDoesntMatch);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on non matching date
     */
    public function testThrowsTransformerExceptionOnNonMatchingDate()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'start_date' => ['date' => true]
        ]);
        $transformer->transform([
            'start_date' => '24.12.2024 18:00'
        ]);
    }
}
