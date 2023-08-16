<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;
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
        $opHandler = new OpHandlerSurveyUpdate(
            'survey',
            Survey::model(),
            new TransformerInputSurvey()
        );

        $opHandler->handle($this->op);
    }

    public function testSurveyUpdateCanHandle()
    {
        $this->initializePatcher();

        $opHandler = new OpHandlerSurveyUpdate(
            'survey',
            Survey::model(),
            new TransformerInputSurvey()
        );
        self::assertTrue($opHandler->canHandle($this->op));
    }

    public function testSurveyUpdateCanNotHandle()
    {
        $this->initializeWrongPatcher();

        $opHandler = new OpHandlerSurveyUpdate(
            'survey',
            Survey::model(),
            new TransformerInputSurvey()
        );
        self::assertTrue($opHandler->canHandle($this->op));
    }

    private function initializePatcher()
    {
        $this->op = OpStandard::factory(
            'survey',
            'update',
            self::$testSurvey->sid,
            [
                'expires' => '2020-01-01 00:00',
                'ipanonymize' => true,
            ]
        );
    }

    private function initializeWrongPatcher()
    {
        $this->op = OpStandard::factory(
            'survey',
            'create',
            self::$testSurvey->sid,
            [
                'xxx' => '2020-01-01 00:00',
                'yyy' => true,
            ]
        );
    }
}
