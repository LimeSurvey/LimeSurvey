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
     * Extract URL parameters from both query string and path-info style URLs.
     * Handles formats like:
     *   ?sid=931272&lang=en  (GET format)
     *   /survey/index/sid/931272/lang/en  (path format)
     *   /931272?lang=en  (short route via URL rules)
     */
    private function extractUrlParams(string $url): array
    {
        $parsed = parse_url($url);
        $params = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
        }
        // Also extract path-info style params (/key/value pairs) and bare numeric sid
        if (!empty($parsed['path'])) {
            $segments = explode('/', trim($parsed['path'], '/'));
            for ($i = 0; $i < count($segments); $i++) {
                // Detect known param keys followed by their value
                if ($i < count($segments) - 1 && in_array($segments[$i], ['sid', 'lang', 'token', 'r'], true)) {
                    $params[$segments[$i]] = $segments[$i + 1];
                    $i++; // skip the value segment
                } elseif (!isset($params['sid']) && preg_match('/^\d+$/', $segments[$i])) {
                    // Bare numeric segment treated as survey id (URL rule: <sid:\d+>)
                    $params['sid'] = $segments[$i];
                }
            }
        }
        return $params;
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
        $queryParams = $this->extractUrlParams($url);
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
        $queryParams = $this->extractUrlParams($url);
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
        $queryParams = $this->extractUrlParams($url);
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
        $queryParams = $this->extractUrlParams($url2);
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
