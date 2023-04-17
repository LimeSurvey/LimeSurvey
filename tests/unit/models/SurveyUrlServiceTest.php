<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyUrl;

class SurveyUrlServiceTest extends \ls\tests\TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_931272_SurveyUrl.lss';
        self::importSurvey($surveyFile);

    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testSurveyUrl(){
        $surveyURlService = new SurveyUrl('en',[],false);
        $url = $surveyURlService->getUrl(931272, self::$testSurvey->languagesettings);
        $sPublicUrl = \Yii::app()->getConfig("publicurl");
        self::assertEquals('survey_url', $url);
    }

    /*
    public function testOneLanguageUrl(){

    }

    public function testMultipleLanguageUrl(){

    }

    public function testMultipleLanguageAliasUrl(){

    } */
}
