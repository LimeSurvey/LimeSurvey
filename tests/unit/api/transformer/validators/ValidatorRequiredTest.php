<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorRequired;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Required Validator
 */
class ValidatorRequiredTest extends TestBaseClass
{
    /**
     * @testdox API Required Validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $configTrue = [
            'required' => true
        ];
        $configCreate = [
            'required' => 'create'
        ];
        $configUpdate = [
         'required' => 'update'
        ];
        $configAsValue = [
            'required'
        ];
        $options = [
            'operation' => 'update'
        ];
        $validator = new ValidatorRequired();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty, $options);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configCreate, $options);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configAsValue, $options);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configUpdate, $options);
        $this->assertTrue($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configTrue, $options);
        $this->assertTrue($normalisedConfig);
    }

    /**
     * @testdox API Required Validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'test';
        $value = null;
        $configTrue = [
            'required' => true
        ];
        $configFalse = [
            'required' => false
        ];
        $dataExists = [
            'test' => 'value'
        ];
        $dataNotExists = [
            'not' => 'value'
        ];
        $validator = new ValidatorRequired();
        $result = $validator->validate($key, $value, $configTrue, $dataExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $value, $configFalse, $dataExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $value, $configFalse, $dataNotExists);
        $this->assertTrue($result);
        $result = $validator->validate($key, $value, $configTrue, $dataNotExists);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on missing required field
     */
    public function testThrowsTransformerExceptionOnMissingRequiredField()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['required' => true],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ]);
    }

    /**
     * @testdox transform() throws TransformerException on missing required field with simple config
     */
    public function testThrowsTransformerExceptionOnMissingRequiredFieldSimple()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['required'],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ]);
    }

    /**
     * @testdox transform() throws TransformerException on missing required by matching operation
     */
    public function testThrowsTransformerExceptionOnMissingRequiredFieldByMatchingOperation()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['required' => 'create'],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ], ['operation' => 'create']);
    }


    /**
     * @testdox transform() throws TransformerException on missing required by matching operation array
     */
    public function testThrowsTransformerExceptionOnMissingRequiredFieldByMatchingOperationArray()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'first_name' => ['required' => ['create', 'update']],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ], ['operation' => 'create']);
    }
}
