<?php

namespace ls\tests;

/**
 * Tests for Survey::getSurveyUrl(), covering regular URLs, alias (short) URLs
 * and the 'lang' parameter handling when languages share an alias.
 *
 * The test survey has base language 'en' and additional languages 'fr' and 'es'.
 */
class SurveyGetSurveyUrlTest extends TestBaseClass
{
    /**
     * Import the multi-language test survey.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_931272_SurveyUrl.lss');
    }

    /**
     * Remove all aliases before each test so tests don't depend on each other.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setAliases([]);
    }

    /**
     * Set the alias of each survey language; languages not listed get no alias.
     *
     * @param array<string, string> $aliases Alias keyed by language code
     * @return void
     */
    private function setAliases(array $aliases)
    {
        foreach (self::$testSurvey->languagesettings as $language => $languageSetting) {
            $languageSetting->surveyls_alias = $aliases[$language] ?? null;
            $languageSetting->save();
        }
    }

    /**
     * Extract URL parameters from both query string and path-info style URLs.
     * Handles formats like:
     *   ?sid=931272&lang=en  (GET format)
     *   /survey/index/sid/931272/lang/en  (path format)
     *   /931272?lang=en  (short route via URL rules)
     *
     * @param string $url
     * @return array<string, string>
     */
    private function extractUrlParams(string $url): array
    {
        $parsed = parse_url($url);
        $params = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
        }
        if (!empty($parsed['path'])) {
            $segments = explode('/', trim($parsed['path'], '/'));
            for ($i = 0; $i < count($segments); $i++) {
                if ($i < count($segments) - 1 && in_array($segments[$i], ['sid', 'lang', 'token', 'r'], true)) {
                    $params[$segments[$i]] = $segments[$i + 1];
                    $i++;
                } elseif (!isset($params['sid']) && preg_match('/^\d+$/', $segments[$i])) {
                    // Bare numeric segment treated as survey id (URL rule: <sid:\d+>)
                    $params['sid'] = $segments[$i];
                }
            }
        }
        return $params;
    }

    /**
     * Without an alias, a regular URL with sid and base language is returned.
     *
     * @return void
     */
    public function testUrlWithoutAliasUsesSidAndBaseLanguage()
    {
        $params = $this->extractUrlParams(self::$testSurvey->getSurveyUrl());
        $this->assertEquals(self::$surveyId, $params['sid'] ?? null);
        $this->assertEquals('en', $params['lang'] ?? null);
    }

    /**
     * Without an alias, the requested additional language is used.
     *
     * @return void
     */
    public function testUrlWithoutAliasUsesRequestedLanguage()
    {
        $params = $this->extractUrlParams(self::$testSurvey->getSurveyUrl('fr'));
        $this->assertEquals(self::$surveyId, $params['sid'] ?? null);
        $this->assertEquals('fr', $params['lang'] ?? null);
    }

    /**
     * An alias on the requested language produces a short URL without sid or lang.
     *
     * @return void
     */
    public function testUrlUsesAlias()
    {
        $this->setAliases(['en' => 'Hogwarts']);
        $url = self::$testSurvey->getSurveyUrl('en');
        $this->assertStringContainsString('Hogwarts', $url);
        $params = $this->extractUrlParams($url);
        $this->assertArrayNotHasKey('sid', $params);
        $this->assertArrayNotHasKey('lang', $params);
    }

    /**
     * With $preferShortUrl = false, the alias is ignored.
     *
     * @return void
     */
    public function testUrlIgnoresAliasWhenShortUrlNotPreferred()
    {
        $this->setAliases(['en' => 'Hogwarts']);
        $url = self::$testSurvey->getSurveyUrl('en', [], false);
        $this->assertStringNotContainsString('Hogwarts', $url);
        $params = $this->extractUrlParams($url);
        $this->assertEquals(self::$surveyId, $params['sid'] ?? null);
        $this->assertEquals('en', $params['lang'] ?? null);
    }

    /**
     * A language without its own alias falls back to the base language alias,
     * and gets a 'lang' parameter because the alias is shared with the base language.
     *
     * @return void
     */
    public function testUrlFallsBackToBaseLanguageAliasWithLangParameter()
    {
        $this->setAliases(['en' => 'Hogwarts']);
        $url = self::$testSurvey->getSurveyUrl('fr');
        $this->assertStringContainsString('Hogwarts', $url);
        $params = $this->extractUrlParams($url);
        $this->assertEquals('fr', $params['lang'] ?? null);
    }

    /**
     * Languages with distinct aliases get their own alias without a 'lang' parameter.
     *
     * @return void
     */
    public function testUrlUsesLanguageSpecificAlias()
    {
        $this->setAliases(['en' => 'Hogwarts', 'fr' => 'Poudlard']);
        $url = self::$testSurvey->getSurveyUrl('fr');
        $this->assertStringContainsString('Poudlard', $url);
        $this->assertStringNotContainsString('Hogwarts', $url);
        $this->assertArrayNotHasKey('lang', $this->extractUrlParams($url));
    }

    /**
     * Languages explicitly sharing the same alias get a 'lang' parameter.
     *
     * @return void
     */
    public function testUrlAddsLangParameterForSharedAlias()
    {
        $this->setAliases(['en' => 'Hogwarts', 'fr' => 'Poudlard', 'es' => 'Poudlard']);
        $url = self::$testSurvey->getSurveyUrl('es');
        $this->assertStringContainsString('Poudlard', $url);
        $this->assertEquals('es', $this->extractUrlParams($url)['lang'] ?? null);
    }

    /**
     * Extra parameters are kept in both regular and alias URLs.
     *
     * @return void
     */
    public function testUrlKeepsExtraParameters()
    {
        $params = $this->extractUrlParams(self::$testSurvey->getSurveyUrl('en', ['token' => 'abc123']));
        $this->assertEquals('abc123', $params['token'] ?? null);

        $this->setAliases(['en' => 'Hogwarts']);
        $url = self::$testSurvey->getSurveyUrl('en', ['token' => 'abc123']);
        $this->assertStringContainsString('Hogwarts', $url);
        $this->assertEquals('abc123', $this->extractUrlParams($url)['token'] ?? null);
    }

    /**
     * Repeated calls must not leak state between each other.
     *
     * @return void
     */
    public function testRepeatedCallsAreConsistent()
    {
        $this->setAliases(['en' => 'Hogwarts']);
        $first = self::$testSurvey->getSurveyUrl('en', ['token' => 'abc123']);
        $withoutShortUrl = self::$testSurvey->getSurveyUrl('en', [], false);
        $this->assertStringNotContainsString('Hogwarts', $withoutShortUrl);
        $this->assertStringNotContainsString('abc123', $withoutShortUrl);
        $this->assertEquals($first, self::$testSurvey->getSurveyUrl('en', ['token' => 'abc123']));
    }
}
