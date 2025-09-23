<?php

namespace helpers;

use ls\tests\TestBaseClass;
use Survey;

class CopySurveyTest extends TestBaseClass
{
    /**
     * Test the copy survey functionality.
     * This test imports a survey and prepares it to be copied.
     *
     * @return void
     * @throws \Exception
     */
    public function testCopySurvey()
    {
        // Import survey all options that could be selected in the modal for copy survey
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_373616_copySurvey.lss';
        self::importSurvey($surveyFile);

        $survey = Survey::model()->findByPk(self::$testSurvey->sid);

        $options['copyResources'] = true;
        $options['excludeQuotas'] = true;
        $options['excludePermissions'] = true;
        $options['excludeAnswers'] = true;
        $options['resetConditions'] = true;
        $options['resetStartEndDate'] = true;
        $options['resetResponseId'] = true;

        $newSurveyId = rand(10000, 99999);

        $copySurveyService = new \LimeSurvey\Models\Services\CopySurvey(
            $survey,
            $options,
            $newSurveyId
        );
        $result = $copySurveyService->copy();

       $this->assertNotNull($result);
    }
}
