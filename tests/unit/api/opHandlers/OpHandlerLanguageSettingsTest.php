<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurveyLanguageSettings;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerLanguageSettingsUpdate;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerLanguageSettingsUpdate
 */
class OpHandlerLanguageSettingsTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox throws exception if no values are provided for single language
     */
    public function testLanguageSettingsUpdateThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getWrongPropsSingleArray(),
            'en',
            'create'
        );
        $opHandler = $this->getOpHandler();
        $opHandler->handle($this->op);
    }

    /**
     * @testdox throws exception if no values are provided for one of multiple languages
     */
    public function testLanguageSettingsUpdateThrowsNoValuesException2()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getWrongPropsMultipleArray(),
            null,
            'create'
        );
        $opHandler = $this->getOpHandler();
        $opHandler->handle($this->op);
    }

    /**
     * @testdox has correct data output when provided with single language
     */
    public function testLanguageSettingsUpdateDataStructureSingle()
    {
        $this->initializePatcher(
            $this->getPropsSingleArray(),
            'en'
        );
        $opHandler = $this->getOpHandler();
        $outputData = $opHandler->getLanguageSettingsData($this->op);
        $this->assertArrayHasKey('en', $outputData);
        $this->assertArrayHasKey('surveyls_title', $outputData['en']);
    }

    /**
     * @testdox has correct data output when provided with multiple languages
     */
    public function testLanguageSettingsUpdateDataStructureMultiple()
    {
        $this->initializePatcher(
            $this->getPropsMultipleArray(),
            null
        );
        $opHandler = $this->getOpHandler();
        $outputData = $opHandler->getLanguageSettingsData($this->op);
        $this->assertArrayHasKey('en', $outputData);
        $this->assertArrayHasKey('surveyls_title', $outputData['en']);
        $this->assertArrayHasKey('de', $outputData);
        $this->assertArrayHasKey('surveyls_title', $outputData['de']);
    }

    private function initializePatcher(array $propsArray, $entityId, $type = 'update')
    {
        $this->op = OpStandard::factory(
            'languageSetting',
            $type,
            $entityId,
            $propsArray,
            [
                'id' => 123456,
            ]
        );
    }

    private function getPropsSingleArray()
    {
        return [
            'title' => 'Example title',
            'language' => 'en',
        ];
    }

    private function getPropsMultipleArray()
    {
        return [
            'en' => [
                'title' => 'Example title'
            ],
            'de' => [
                'title' => 'Beispieltitel'
            ],
        ];
    }

    private function getWrongPropsSingleArray()
    {
        return [
            'xyz' => 'Example title',
        ];
    }

    private function getWrongPropsMultipleArray()
    {
        return [
            'en' => [
                'title' => 'Example title'
            ],
            'de' => [
                'xyz' => 'Beispieltitel'
            ],
        ];
    }

    /**
     * @return OpHandlerLanguageSettingsUpdate
     */
    private function getOpHandler()
    {
        /** @var \SurveyLanguageSetting */
        $modelSurveyLanguageSetting = \Mockery::mock(
            \SurveyLanguageSetting::class
        )->makePartial();
        return new OpHandlerLanguageSettingsUpdate(
            $modelSurveyLanguageSetting,
            new TransformerInputSurveyLanguageSettings()
        );
    }
}
