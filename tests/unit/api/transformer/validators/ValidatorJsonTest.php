<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Validator\ValidatorJson;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

/**
 * @testdox API Json Validator
 */
class ValidatorJsonTest extends TestBaseClass
{
    /**
     * @testdox API json validator normaliseConfigValue() returns expected boolean
     */
    public function testNormaliseConfigValue()
    {
        $configEmpty = [];
        $configTrue = [
            'json' => true
        ];

        $validator = new ValidatorJson();
        $normalisedConfig = $validator->normaliseConfigValue($configEmpty);
        $this->assertFalse($normalisedConfig);
        $normalisedConfig = $validator->normaliseConfigValue($configTrue);
        $this->assertTrue($normalisedConfig);
    }

    /**
     * @testdox API json validator validate() returns expected result
     */
    public function testValidate()
    {
        $key = 'welcomeImage';
        $valueNull = null;
        $valueEmptyString = '';
        $valueValidJson = '{"image_path":"/upload/surveys/1/images/demo.png","image_align":"left"}';
        $valueInvalidJson = 'not-valid-json{';
        $valueNotAString = ['image_path' => '/upload/surveys/1/images/demo.png'];
        $configTrue = [
            'json' => true
        ];
        $configFalse = [
            'json' => false
        ];
        $data = [
            'welcomeImage' => $valueValidJson
        ];

        $validator = new ValidatorJson();
        $result = $validator->validate($key, $valueValidJson, $configTrue, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueNull, $configTrue, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueEmptyString, $configTrue, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueInvalidJson, $configFalse, $data);
        $this->assertTrue($result);
        $result = $validator->validate($key, $valueInvalidJson, $configTrue, $data);
        $this->assertIsArray($result);
        $result = $validator->validate($key, $valueNotAString, $configTrue, $data);
        $this->assertIsArray($result);
    }

    /**
     * @testdox transform() throws TransformerException on invalid JSON
     */
    public function testThrowsTransformerExceptionOnInvalidJson()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'welcome_image' => ['json' => true]
        ]);
        $transformer->transform([
            'welcome_image' => 'not-valid-json{'
        ]);
    }

    /**
     * @testdox transform() passes through a valid JSON string unchanged
     */
    public function testTransformPassesThroughValidJson()
    {
        $transformer = DI::getContainer()->get(
            Transformer::class
        );
        $transformer->setDataMap([
            'welcome_image' => ['json' => true]
        ]);
        $validJson = '{"image_path":"/upload/surveys/1/images/demo.png"}';
        $result = $transformer->transform([
            'welcome_image' => $validJson
        ]);
        $this->assertEquals($validJson, $result['welcome_image']);
    }
}
