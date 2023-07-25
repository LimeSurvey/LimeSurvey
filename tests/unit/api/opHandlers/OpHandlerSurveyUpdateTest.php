<?php

namespace ls\tests\unit\api\opHandlers;

use DI\FactoryInterface;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use ls\tests\TestBaseClass;
use Psr\Container\ContainerInterface;
use Survey;

class OpHandlerSurveyUpdateTest extends TestBaseClass
{

protected FactoryInterface $diFactory;
protected ContainerInterface $diContainer;

    public function testSurveyUpdate()
    {
        // Import survey
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_QuestionAttributeTestSurvey.lss';
        self::importSurvey($surveyFile);

        $opHandler = new OpHandlerSurveyUpdate(
            'survey',
            Survey::model(),
            $this->diContainer->get(TransformerInputSurvey::class)
        );

        self::assertTrue($opHandler->canHandle());
    }
}
