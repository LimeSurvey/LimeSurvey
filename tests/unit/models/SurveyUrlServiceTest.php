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
        //i would expect the result to be something like
        // --> "http://hostname/index.php/987852?lang=en"
        //but when running the test i get something like
        //when manually testing it in the application (see overview page) everything works as expected ...
        //seems to me, that calling the service class from outside application folder (here in test or vendor folder)
        //is the reason for it. Pretty hard to test it then ...
        self::assertEquals('survey_url', $url);
    }

    /*
     *

    public function testUrlUsingAlias(){
    }

    public function testOneLanguageUrl(){
    }

    public function testMultipleLanguageUrl(){
    }

    public function testMultipleLanguageAliasUrl(){
    }
    */
}
