<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurveyLanguageSettings;
use LimeSurvey\Libraries\Api\Command\V1\SurveyPatch\OpHandlerLanguageSettingsUpdate;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;
use ls\tests\unit\services\SurveyUpdater\GeneralSettings\GeneralSettingsMockSetFactory;

/**
 * @testdox OpHandlerLanguageSettingsUpdate
 */
class OpHandlerLanguageSettingsUpdateTest extends TestBaseClass
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
            $this->getEntityIdSingle()
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
            $this->getEntityIdMultiple(),
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
            $this->getEntityIdSingle(),
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
            $this->getEntityIdMultiple(),
        );
        $opHandler = $this->getOpHandler();
        $outputData = $opHandler->getLanguageSettingsData($this->op);
        $this->assertArrayHasKey('en', $outputData);
        $this->assertArrayHasKey('surveyls_title', $outputData['en']);
        $this->assertArrayHasKey('de', $outputData);
        $this->assertArrayHasKey('surveyls_title', $outputData['de']);
    }

    private function initializePatcher(array $propsArray, array $entityId)
    {
        $this->op = OpStandard::factory(
            'languageSetting',
            'update',
            $entityId,
            $propsArray,
            [
                'id' => 123456,
            ]
        );
    }

    private function getEntityIdSingle()
    {
        return [
            'sid' => '123456',
            'language' => 'en',
        ];
    }

    private function getEntityIdMultiple()
    {
        return [
            'sid' => '123456'
        ];
    }

    private function getPropsSingleArray()
    {
        return [
            'title' => 'Example title',
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
        $modelSurveyLanguageSetting = \Mockery::mock(
            \SurveyLanguageSetting::class
        )->makePartial();

        return new OpHandlerLanguageSettingsUpdate(
            'languageSetting',
            $modelSurveyLanguageSetting,
            new TransformerInputSurveyLanguageSettings()
        );
    }
}
