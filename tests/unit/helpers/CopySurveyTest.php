<?php

namespace helpers;

use ls\tests\TestBaseClass;
use LSHttpRequest;

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

        //produce a fake post request with specific params
        $request = $this
            ->getMockBuilder(LSHttpRequest::class)
            ->getMock();
        $_POST['surveyIdToCopy'] = self::$testSurvey->sid;
        $_POST['copysurveytranslinksfields'] = '1';
        $_POST['copysurveyexcludequotas'] = '1';
        $_POST['copysurveyexcludepermissions'] = '1';
        $_POST['copysurveyexcludeanswers'] = '1';
        $_POST['copysurveyresetconditions'] = '1';
        $_POST['copysurveyresetstartenddate'] = '1';
        $_POST['copysurveyresetresponsestartid'] = '1';

        $newSurveyName = self::$testSurvey->currentLanguageSettings->surveyls_title . '- Copy';
        $newSurveyId = rand(10000, 99999);

        $copySurveyService = new \LimeSurvey\Models\Services\CopySurvey(
            $request,
            $newSurveyName,
            $newSurveyId
        );
        $result = $copySurveyService->copy();

        $this->assertNotNull($result);
    }
}
