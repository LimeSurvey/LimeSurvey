<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyUrl;
use LSYii_Application;

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

    /**
     * Compares the parameters at the end.
     *
     * Inside the application it should be like:
     * http://develop/index.php/931272?lang=en
     * --> http://hostname/index.php/931272?lang=en
     *
     * In the test environment it is always like:
     * http://./vendor/bin/phpunit/931272?lang=en
     *
     * @return void
     */
    public function testSurveyUrl(){
        $surveyURlService = new SurveyUrl('en', [], false);
        $url = $surveyURlService->getUrl(
            931272,
            self::$testSurvey->languagesettings
        );
        //compare parameters at the end
        self::assertStringContainsString('931272&lang=en', $url);
    }

    /**
     * Test if alias is used
     *
     * @return void
     */
    public function testUrlUsingAlias(){
        /** @var \SurveyLanguageSetting $languageSetting */
        $languageSetting = self::$testSurvey->languagesettings['en'];
        $languageSetting->surveyls_alias = 'Hogwarts';
        $languageSetting->save();

        $surveyURlService = new SurveyUrl('en', []);
        $url = $surveyURlService->getUrl(
            931272,
            self::$testSurvey->languagesettings,
            self::$testSurvey->getAliasForLanguage()
        );
        self::assertStringContainsString('Hogwarts', $url);
    }

    public function testMultipleLanguageUrl(){
        //remove alias
        $languageSetting = self::$testSurvey->languagesettings['en'];
        $languageSetting->surveyls_alias = null;
        $languageSetting->save();
        $surveyURlService = new SurveyUrl('fr', [], false);
        $url = $surveyURlService->getUrl(
            931272,
            self::$testSurvey->languagesettings
        );
        //compare parameters at the end
        self::assertStringContainsString('931272&lang=fr', $url);
    }

    public function testMultipleLanguageAliasUrl(){
        $languageSetting = self::$testSurvey->languagesettings['en'];
        $languageSetting->surveyls_alias = 'Hogwarts';
        $languageSetting->save();
        $surveyURlService = new SurveyUrl('fr', []);
        $url = $surveyURlService->getUrl(
            931272,
            self::$testSurvey->languagesettings,
            self::$testSurvey->getAliasForLanguage()
        );
        self::assertStringContainsString('Hogwarts&lang=fr', $url);
    }
}
