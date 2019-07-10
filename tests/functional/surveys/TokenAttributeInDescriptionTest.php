<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @group tokenindescr
 */
class TokenAttributeInDescriptionTest extends TestBaseClassWeb
{
    /**
     *
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile =  'tests/data/surveys/survey_archive_426848.lsa';
        self::importSurvey($surveyFile);
    }

    /**
     * 
     */
    public function testTokenAttributeInDescription()
    {
        $web  = self::$webDriver;
        $url = $this->getSurveyUrl();

        // Make sure description is correct, in case this test is run
        // after the test below.
        $surveyLanguage = \SurveyLanguageSetting::model()->findByPk(
            [
                'surveyls_survey_id' => self::$surveyId,
                'surveyls_language'  => 'en'
            ]
        );
        $surveyLanguage->surveyls_description = 'description with {TOKEN:ATTRIBUTE_1}';
        $surveyLanguage->surveyls_welcometext = 'welcome message with {TOKEN:ATTRIBUTE_2}';
        $surveyLanguage->save();


        try {
            // Go to token input screen.
            $web->get($url);

            // Write in token.
            $web->answerTextQuestion('token', 'token1');

            // Click "continue".
            $submitButton = $web->findElement(WebDriverBy::cssSelector('button[type="submit"'));
            $submitButton->click();

            $descr = $web->findElement(WebDriverBy::cssSelector('.survey-description'));
            $this->assertNotFalse(strpos($descr->getText(), 'some attribute 1'));

            $welcome = $web->findElement(WebDriverBy::cssSelector('.survey-welcome'));
            $this->assertNotFalse(strpos($welcome->getText(), 'another attribute 2'));

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'TokenAttributeInDescription');
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     *
     */
    public function testNoTokenInDescription()
    {
        $web  = self::$webDriver;
        $url = $this->getSurveyUrl();

        $surveyLanguage = \SurveyLanguageSetting::model()->findByPk(
            [
                'surveyls_survey_id' => self::$surveyId,
                'surveyls_language'  => 'en'
            ]
        );
        $surveyLanguage->surveyls_description = 'test description';
        $surveyLanguage->surveyls_welcometext = 'test welcome text';
        $surveyLanguage->save();

        try {
            // Go to token input screen.
            // Go to token input screen.
            $web->get($url);

            // Write in token.
            $web->answerTextQuestion('token', 'token1');

            // Click "continue".
            $submitButton = $web->findElement(WebDriverBy::cssSelector('button[type="submit"'));
            $submitButton->click();

            $descr = $web->findElement(WebDriverBy::cssSelector('.survey-description'));
            $this->assertNotFalse(strpos($descr->getText(), 'test description'));

            $welcome = $web->findElement(WebDriverBy::cssSelector('.survey-welcome'));
            $this->assertNotFalse(strpos($welcome->getText(), 'test welcome text'));

        } catch (\Exception $ex) {
        }
    }
}
