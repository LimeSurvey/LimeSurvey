<?php

namespace ls\tests;

use CUrlManager;
use Survey;
use Yii;

/**
 * @group shorturl
 */
class ShortUrlTest extends TestBaseClassWeb
{
    /**
     * Build a short URL for the given alias and query params, respecting the
     * configured urlFormat ('path' or 'get') from application/config/config.php.
     *
     * path format: http://domain/index.php/alias?key=val
     * get  format: http://domain/index.php?r=alias&key=val
     */
    private function buildShortUrl(string $alias, array $params = []): string
    {
        $baseUrl = 'http://' . self::$domain . '/index.php';
        $urlManager = Yii::app()->getUrlManager();
        $urlFormat = $urlManager->getUrlFormat();

        if ($urlFormat === CUrlManager::GET_FORMAT) {
            $params = [$urlManager->routeVar => $alias] + $params;
            $url = $baseUrl;
        } else {
            $url = $baseUrl . '/' . $alias;
        }

        $query = $urlManager->createPathInfo($params, '=', '&');
        if (!empty($query)) {
            $url .= '?' . $query;
        }

        return $url;
    }

    /**
     * Test short urls without extra params (except language)
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnOpenSurvey($alias, $params, $welcomeText, $firstQuestionText)
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_shortUrlOpen.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        parse_str($params, $queryParams);
        $url = $this->buildShortUrl($alias, $queryParams);
        $web = self::$webDriver;

        try {
            // Go to welcome
            $web->get($url);

            // Check welcome text
            $welcome = $web->findByCss(".survey-welcome");
            $this->assertNotFalse(strpos($welcome->getText(), $welcomeText));

            // Go to first group
            $web->next();

            // Check first question
            $question = $web->findByCss(".ls-label-question");
            $this->assertNotFalse(strpos($question->getText(), $firstQuestionText));

            // Submit
            $web->next();

            // Check the completed text is there
            $web->findByCss(".completed-text");
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__ . $alias);
            $this->fail(
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Test short urls with question prefill
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnOpenSurveyWithPrefill($alias, $params)
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_shortUrlOpen.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        parse_str($params, $queryParams);
        $queryParams['Q01'] = 'Prefilled';
        $url = $this->buildShortUrl($alias, $queryParams);
        list(, , $sgqa) = self::$testHelper->getSgqa('Q01', self::$surveyId);
        $web = self::$webDriver;

        try {
            // Go to welcome
            $web->get($url);

            // Go to first group
            $web->next();

            // Check first question
            $answer = $web->findByName($sgqa);
            $this->assertEquals($answer->getAttribute("value"), 'Prefilled');

            // Submit
            $web->next();

            // Check the completed text is there
            $web->findByCss(".completed-text");
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->fail(
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Test short urls with tokens
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnClosedSurvey($alias, $params, $welcomeText, $firstQuestionText)
    {
        self::importSurvey(self::$surveysFolder . '/survey_archive_shortUrlClosed.lsa');

        parse_str($params, $queryParams);
        $queryParams['token'] = '123456';
        $url = $this->buildShortUrl($alias, $queryParams);
        $web = self::$webDriver;

        try {
            // Go to welcome
            $web->get($url);

            // Check welcome text
            $welcome = $web->findByCss(".survey-welcome");
            $this->assertNotFalse(strpos($welcome->getText(), $welcomeText));

            // Go to first group
            $web->next();

            // Check first question
            $question = $web->findByCss(".ls-label-question");
            $this->assertNotFalse(strpos($question->getText(), $firstQuestionText));

            // Submit
            $web->next();

            // Check the completed text is there
            $web->findByCss(".completed-text");
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->fail(
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Test short urls with question prefill and tokens
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnClosedSurveyWithPrefill($alias, $params)
    {
        self::importSurvey(self::$surveysFolder . '/survey_archive_shortUrlClosed.lsa');

        parse_str($params, $queryParams);
        $queryParams['token'] = '123456';
        $queryParams['Q01'] = 'Prefilled';
        $url = $this->buildShortUrl($alias, $queryParams);

        list(, , $sgqa) = self::$testHelper->getSgqa('Q01', self::$surveyId);

        $web = self::$webDriver;

        try {
            // Go to welcome
            $web->get($url);

            // Go to first group
            $web->next();

            // Check first question
            $answer = $web->findByName($sgqa);
            $this->assertEquals($answer->getAttribute("value"), 'Prefilled');

            // Submit
            $web->next();

            // Check the completed text is there
            $web->findByCss(".completed-text");
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__ . '_stepFinal');
            $this->fail(
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Provides test data
     */
    public function shortUrlDataProvider()
    {
        return [
            ["testsurvey", "", "This is the welcome text in english", "Question 1"],
            ["prueba", "lang=it", "Questo è il testo di benvenuto in italiano.", "Domanda 1"],
            ["prueba", "lang=es", "Este es el texto de bienvenida en español.", "Pregunta 1"],
        ];
    }


    protected function setUp(): void
    {
        parent::setUp();
//        self::$webDriver->manage()->deleteAllCookies();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpPreviousSurvey();
    }


    private function cleanUpPreviousSurvey(): void
    {
        if (!empty(self::$surveyId)) {
            Survey::model()->deleteSurvey(self::$surveyId);
            self::$testSurvey = null;
            self::$surveyId = null;
        }
    }
}
