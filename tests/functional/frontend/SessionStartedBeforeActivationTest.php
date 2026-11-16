<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * A survey session started while the survey was inactive (preview) must be discarded once the survey is active,
 * otherwise the response is never written to the response table.
 *
 * @see https://bugs.limesurvey.org/view.php?id=19378
 * @group preview
 */
class SessionStartedBeforeActivationTest extends TestBaseClassWeb
{
    /** @var string Access code of the participant taking the survey after the preview */
    private const TOKEN = 'issue19378';

    /**
     * Import the survey (still inactive) and log in as administrator.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_489_default.lss');
        // Detailed admin notification: Sending it needs the saved response
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $survey->emailresponseto = 'admin@example.org';
        $survey->save();

        self::adminLogin();
    }

    /**
     * Preview the inactive survey, activate it, then take it with an access code in the same browser session.
     *
     * @return void
     */
    public function testResponseIsSavedAfterPreviewOfInactiveSurvey()
    {
        $web = self::$webDriver;

        // Preview the inactive survey: This starts a survey session for the inactive survey
        $web->get($this->getSurveyUrl());
        $web->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ls-button-submit'))
        );

        self::$testHelper->activateSurvey(self::$surveyId);
        \Token::createTable(self::$surveyId);
        $token = \Token::create(self::$surveyId);
        $token->firstname = 'Issue';
        $token->lastname = '19378';
        $token->email = 'participant@example.org';
        $token->token = self::TOKEN;
        $this->assertTrue($token->save(), 'Could not create the participant');

        // Open the invitation link without "newtest": The session from the preview is still there
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web->get($urlMan->createUrl('survey/index', ['sid' => self::$surveyId, 'token' => self::TOKEN, 'lang' => 'en']));

        // Welcome page
        $web->clickButton('ls-button-submit');
        $answer = $web->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('textarea'))
        );
        $answer->sendKeys('Answer given after activation');
        $web->clickButton('ls-button-submit');
        $web->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('completed-text'))
        );

        $responses = \SurveyDynamic::model(self::$surveyId)->findAllByAttributes(['token' => self::TOKEN]);
        $this->assertCount(1, $responses, 'The response was not saved');
        $this->assertNotEmpty($responses[0]->submitdate, 'The response was not submitted');
    }
}
