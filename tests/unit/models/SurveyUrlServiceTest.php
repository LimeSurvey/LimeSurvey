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
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);
        self::assertEquals('931272', $queryParams['sid'] ?? null);
        self::assertEquals('en', $queryParams['lang'] ?? null);
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
        // Alias should appear in the URL (either path or query depending on URL format)
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
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);
        self::assertEquals('931272', $queryParams['sid'] ?? null);
        self::assertEquals('fr', $queryParams['lang'] ?? null);
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
        self::assertStringContainsString('Hogwarts', $url);
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $queryParams);
        self::assertEquals('fr', $queryParams['lang'] ?? null);
    }

    /**
     * Regression test: repeated getUrl calls on the same SurveyUrl instance
     * must not leak state (e.g. 'lang' or routeVar from a previous call).
     */
    public function testRepeatedGetUrlCalls()
    {
        $languageSetting = self::$testSurvey->languagesettings['en'];
        $languageSetting->surveyls_alias = 'Hogwarts';
        $languageSetting->save();

        // Instance with alias preference
        $service = new SurveyUrl('en', []);

        // First call with alias
        $url1 = $service->getUrl(
            931272,
            self::$testSurvey->languagesettings,
            self::$testSurvey->getAliasForLanguage('en')
        );
        self::assertStringContainsString('Hogwarts', $url1);

        // Second call without alias — should produce a plain ID-based URL, not leak 'Hogwarts'
        $url2 = $service->getUrl(
            931272,
            self::$testSurvey->languagesettings,
            null
        );
        self::assertStringNotContainsString('Hogwarts', $url2);
        $parsed = parse_url($url2);
        parse_str($parsed['query'] ?? '', $queryParams);
        self::assertEquals('931272', $queryParams['sid'] ?? null);
        self::assertEquals('en', $queryParams['lang'] ?? null);

        // Third call with alias again — must still work correctly
        $url3 = $service->getUrl(
            931272,
            self::$testSurvey->languagesettings,
            self::$testSurvey->getAliasForLanguage('en')
        );
        self::assertStringContainsString('Hogwarts', $url3);
        self::assertEquals($url1, $url3);
    }
}
