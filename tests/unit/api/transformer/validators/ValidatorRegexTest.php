<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorRegex;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Regex Validator
 */
class ValidatorRegexTest extends TestBaseClass
{
    /**
     * @testdox API regex validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $config = [
            'pattern' => '/^[a-zA-Z0-9]+$/'
        ];

        $validator = new ValidatorRegex();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($config);
        $this->assertEquals('/^[a-zA-Z0-9]+$/', $normalisedConfig);
    }

    /**
     * @testdox API regex validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $valueNull = null;
        $valueMatches = 'value11';
        $valueDoesntMatch = 'WRONG!!!';
        $configTrue = [
            'pattern' => '/^[a-zA-Z0-9]+$/'
        ];
        $configFalse = [
            'pattern' => false
        ];
        $dataExists = [
            'test' => 'value11'
        ];
        $dataDoesntMatch = [
            'test' => 'Wrong!!!'
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new ValidatorRegex();
        $result = $validator->validate($key, $valueMatches, $configTrue, $dataExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueMatches, $configFalse, $dataDoesntMatch);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configFalse, $dataNotExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueDoesntMatch, $configTrue, $dataDoesntMatch);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on non matching pattern
     */
    public function testThrowsTransformerExceptionOnNonMatchingPattern()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['pattern' => '/^[a-zA-Z0-9]+$/']
        ]);
        $transformer->transform([
            'first_name' => 'Wrong!!!'
        ]);
    }
}
