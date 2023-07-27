<?php

namespace ls\tests\unit\api\opHandlers;

use DI\FactoryInterface;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;
use Psr\Container\ContainerInterface;
use Survey;

class OpHandlerSurveyUpdateTest extends TestBaseClass
{

protected FactoryInterface $diFactory;
protected ContainerInterface $diContainer;

protected OpInterface $op;

    public function testSurveyUpdate()
    {
        $this->initializePatcher();
        $this->diContainer = $this->diFactory->make(ContainerInterface::class);
        // Import survey (it doesn't matter which survey)
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_QuestionAttributeTestSurvey.lss';
        self::importSurvey($surveyFile);

        $opHandler = new OpHandlerSurveyUpdate(
            'survey',
            Survey::model(),
            $this->diContainer->get(TransformerInputSurvey::class)
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
                'title' => 'Hogwarts',
                'description' => 'Home of Harry Potter and the Sorcerer\'s Stone',
            ]
        );
    }
}
