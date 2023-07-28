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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Import survey (it doesn't matter which survey)
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_QuestionAttributeTestSurvey.lss';
        self::importSurvey($surveyFile);
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

    /**
     * @throws OpHandlerException
     */
    public function testSurveyUpdateHandles()
    {
        $this->initializePatcher();

        $opHandler = new OpHandlerSurveyUpdate(
          'survey',
            Survey::model(),
            new TransformerInputSurvey()
        );

        $opHandler->handle($this->op);

        //check if survey has been updated
        self::assertEquals(self::$testSurvey->ipanonymize, 'Y');
        self::assertEquals(self::$testSurvey->expires,'2020-01-01 00:00');
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
}
