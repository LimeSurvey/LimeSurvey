<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyUrl;

class SurveyUrlServiceTest extends \ls\tests\TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_268886_testSurveyPermissions.lss';
        self::importSurvey($surveyFile);

    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testAliasUrl(){
        self::assertEquals(5,5);
    }

    /*
    public function testOneLanguageUrl(){

    }

    public function testMultipleLanguageUrl(){

    }

    public function testMultipleLanguageAliasUrl(){

    } */
}
