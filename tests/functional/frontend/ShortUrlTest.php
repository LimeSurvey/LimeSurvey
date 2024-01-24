<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Survey;

/**
 * @group shorturl
 */
class ShortUrlTest extends TestBaseClassWeb
{
    /**
     * Test short urls without extra params (except language)
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnOpenSurvey($alias, $params, $welcomeText, $firstQuestionText)
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_shortUrlOpen.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        $url = 'http://' . self::$domain . '/index.php/' . $alias;
        if (!empty($params)) {
            $url .= "?" . $params;
        }
        $web  = self::$webDriver;

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
            $completedText = $web->findByCss(".completed-text");

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }

    /**
     * Test short urls with question prefill
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnOpenSurveyWithPrefill($alias, $params)
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_shortUrlOpen.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        $url = 'http://' . self::$domain . '/index.php/' . $alias . "?Q01=Prefilled";
        if (!empty($params)) {
            $url .= "&" . $params;
        }
        list(, , $sgqa) = self::$testHelper->getSgqa('Q01', self::$surveyId);
        $web  = self::$webDriver;

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
            $completedText = $web->findByCss(".completed-text");

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }

    /**
     * Test short urls with tokens
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnClosedSurvey($alias, $params, $welcomeText, $firstQuestionText)
    {
        self::importSurvey(self::$surveysFolder . '/survey_archive_shortUrlClosed.lsa');

        $url = 'http://' . self::$domain . '/index.php/' . $alias . "?token=123456";
        if (!empty($params)) {
            $url .= "&" . $params;
        }
        $web  = self::$webDriver;

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
            $completedText = $web->findByCss(".completed-text");

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }

    /**
     * Test short urls with question prefill and tokens
     * @dataProvider shortUrlDataProvider
     */
    public function testShortUrlOnClosedSurveyWithPrefill($alias, $params)
    {
        self::importSurvey(self::$surveysFolder . '/survey_archive_shortUrlClosed.lsa');

        $url = 'http://' . self::$domain . '/index.php/' . $alias . "?token=123456&Q01=Prefilled";
        if (!empty($params)) {
            $url .= "&" . $params;
        }

        list(, , $sgqa) = self::$testHelper->getSgqa('Q01', self::$surveyId);

        $web  = self::$webDriver;

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
            $completedText = $web->findByCss(".completed-text");

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
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
}
