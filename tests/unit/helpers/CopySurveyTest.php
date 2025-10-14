<?php

namespace helpers;

use LimeSurvey\Models\Services\CopySurveyOptions;
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

        //intial state is, that everything is copied and all values are reset
        $optionsDataContainer = new CopySurveyOptions();

        $copySurveyService = new \LimeSurvey\Models\Services\CopySurvey(
            $survey,
            $optionsDataContainer,
            ''
        );
        $result = $copySurveyService->copy();

        $this->assertEquals($result->getErrors(), []);
    }
}
