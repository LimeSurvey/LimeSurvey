<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * A quota on an always hidden single choice question, whose value is set by an equation question.
 * The quota is "Younger than 30" (agegroup = Y) with a limit of 1.
 *
 * @see https://bugs.limesurvey.org/view.php?id=15966
 * @see https://bugs.limesurvey.org/view.php?id=15412
 * @group quota
 */
class QuotaOnHiddenQuestionTest extends TestBaseClassWeb
{
    /** @var string Message of the quota in the fixture survey */
    private const QUOTA_MESSAGE = 'The quota for participants younger than 30 is full.';

    /**
     * Import and activate the survey.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_159660_quota_hidden_question.lss');
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * The quota is not full yet: A participant in the quota completes the survey.
     *
     * @return void
     */
    public function testParticipantInQuotaCompletesWhileQuotaIsNotFull()
    {
        $this->answerAge(20);
        $this->assertSurveyContinues();
        self::$webDriver->next();
        $this->waitForCompletedText();

        $response = $this->getLastResponse();
        $this->assertSame('Y', $response->getAttribute($this->getFieldName('agegroup')), 'The equation did not set the hidden question');
        $this->assertNotEmpty($response->submitdate, 'The response was not submitted');
        $this->assertSame(1, (int) \Quota::model()->findByAttributes(['sid' => self::$surveyId])->completeCount);
    }

    /**
     * A participant outside of the quota is not stopped by the full quota.
     *
     * @depends testParticipantInQuotaCompletesWhileQuotaIsNotFull
     * @return void
     */
    public function testParticipantOutsideOfFullQuotaCompletes()
    {
        $this->answerAge(45);
        $this->assertSurveyContinues();
        self::$webDriver->next();
        $this->waitForCompletedText();

        $response = $this->getLastResponse();
        $this->assertSame('O', $response->getAttribute($this->getFieldName('agegroup')), 'The equation did not set the hidden question');
        $this->assertNotEmpty($response->submitdate, 'The response was not submitted');
    }

    /**
     * The quota is full: A participant in the quota is stopped after the page with the hidden question.
     *
     * @dataProvider terminateActionProvider
     * @depends testParticipantInQuotaCompletesWhileQuotaIsNotFull
     * @param int $action Quota action
     * @return void
     */
    public function testParticipantInFullQuotaIsStopped(int $action)
    {
        $this->setQuotaAction($action);

        $this->answerAge(25);
        $quotaMessage = $this->waitForQuotaMessage();
        $this->assertStringContainsString(self::QUOTA_MESSAGE, $quotaMessage->getText());
        $this->assertEmpty(
            self::$webDriver->findElements(WebDriverBy::cssSelector('.QuotaMessage .ls-move-previous-btn')),
            'A terminate quota must not offer to change the answers'
        );

        $response = $this->getLastResponse();
        $this->assertSame('Y', $response->getAttribute($this->getFieldName('agegroup')), 'The equation did not set the hidden question');
        $this->assertEmpty($response->submitdate, 'A response stopped by the quota must not be submitted');
    }

    /**
     * Quota actions that end the survey.
     *
     * @return array<string, array{int}>
     */
    public static function terminateActionProvider(): array
    {
        return [
            'Terminate after related visible question was submitted' => [\Quota::TERMINATE_VISIBLE_QUOTA_QUESTIONS],
            'Terminate after related visible and hidden questions were submitted' => [\Quota::TERMINATE_VISIBLE_AND_HIDDEN_QUOTA_QUESTIONS],
            'Terminate after all page submissions' => [\Quota::TERMINATE_ALL_PAGES],
        ];
    }

    /**
     * Start a new response and submit the first page with the given age.
     *
     * @param int $age Answer to the age question
     * @return void
     */
    private function answerAge(int $age): void
    {
        $web = self::$webDriver;
        $web->get($this->getSurveyUrl());
        $input = $web->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::name($this->getFieldName('age')))
        );
        $input->sendKeys((string) $age);
        $web->next();
    }

    /**
     * Check that the second page of the survey is shown.
     *
     * @return void
     */
    private function assertSurveyContinues(): void
    {
        $comment = self::$webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::name($this->getFieldName('comment'))
            )
        );
        $this->assertNotEmpty($comment);
    }

    /**
     * Wait for the end page of a submitted survey.
     *
     * @return void
     */
    private function waitForCompletedText(): void
    {
        $completed = self::$webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('completed-text'))
        );
        $this->assertStringNotContainsString(self::QUOTA_MESSAGE, $completed->getText());
    }

    /**
     * Wait for the quota page.
     *
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function waitForQuotaMessage()
    {
        return self::$webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('quotamessage'))
        );
    }

    /**
     * Set the action of the quota of the survey.
     *
     * @param int $action Quota action
     * @return void
     */
    private function setQuotaAction(int $action): void
    {
        $quota = \Quota::model()->findByAttributes(['sid' => self::$surveyId]);
        $quota->action = $action;
        $this->assertTrue($quota->save(), json_encode($quota->errors));
    }

    /**
     * Get the response field name of a question.
     *
     * @param string $title Question code
     * @return string
     */
    private function getFieldName(string $title): string
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => $title]);
        return 'Q' . $question->qid;
    }

    /**
     * Get the last started response.
     *
     * @return \Response
     */
    private function getLastResponse(): \Response
    {
        $response = \Response::model(self::$surveyId)->find(['order' => 'id DESC']);
        $this->assertNotNull($response, 'No response was saved');
        return $response;
    }
}
