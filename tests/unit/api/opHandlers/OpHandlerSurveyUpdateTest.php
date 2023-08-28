<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;
use ls\tests\unit\services\SurveyUpdater\GeneralSettings\GeneralSettingsMockSetFactory;
use Survey;

class OpHandlerSurveyUpdateTest extends TestBaseClass
{
    protected OpInterface $op;

    public function testSurveyUpdateThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializeWrongPatcher();
        $opHandler = $this->getOpHandler();

        $opHandler->handle($this->op);
    }

    public function testSurveyUpdateCanHandle()
    {
        $this->initializePatcher();

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    public function testSurveyUpdateCanNotHandle()
    {
        $this->initializeWrongPatcher();

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    private function initializePatcher()
    {
        $this->op = OpStandard::factory(
            'survey',
            'update',
            12345,
            [
                'expires' => '2020-01-01 00:00',
                'ipanonymize' => true,
            ],
            [
                'id' => 123456,
            ]
        );
    }

    private function initializeWrongPatcher()
    {
        $this->op = OpStandard::factory(
            'survey',
            'create',
            12345,
            [
                'xxx' => '2020-01-01 00:00',
                'yyy' => true,
            ],
            [
                'id' => 123456,
            ]
        );
    }

    /**
     * @return OpHandlerSurveyUpdate
     */
    private function getOpHandler()
    {
        $mockSet = (new GeneralSettingsMockSetFactory())->make();

        return new OpHandlerSurveyUpdate(
            'survey',
            $mockSet->modelSurvey,
            new TransformerInputSurvey()
        );
    }
}
