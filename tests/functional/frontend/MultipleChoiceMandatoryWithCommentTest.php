<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2018-02-08
 * @group multmand
 */
class MultipleChoiceMandatoryWithComment extends TestBaseClassWeb
{

    protected $questions;

    /**
     *
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile =  'tests/data/surveys/limesurvey_survey_479717.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Import and activate survey at every test.
     */
    public function setUp(): void
    {
        self::$testHelper->activateSurvey(self::$surveyId);
        $rawQuestions = \Question::model()->findAll("sid = :sid", [":sid" => self::$surveyId]);
        $this->questions = [];
        foreach ($rawQuestions as $rawQuestion) {
            $this->questions[$rawQuestion->title] = $rawQuestion;
        }
    }

    /**
     * Delete test survey after every test.
     */
    public function tearDown(): void
    {
        self::$testHelper->deactivateSurvey(self::$surveyId);

        // NB: Need to sleep since deactivated survey is named
        // by second, and tests can deactivate same survey more
        // than once for one second.
        sleep(1);
    }
    /**
     * Test submit question without comment.
     */
    public function testNoComment()
    {
        // To make writing shorter.
        $web  = self::$webDriver;

        list($sgqa, $subquestions) = $this->getSgqa();
        $url = $this->getSurveyUrl();
        $sid = self::$testSurvey->sid;
        $dbo = \Yii::app()->getDb();

        try {
            self::$webDriver->get($url);

            // Click "First"
            $label = $web->findElement(WebDriverBy::id('label-answer' . $sgqa . '_S' . $this->questions['SQ001']->qid));
            $label->click();

            // Submit
            $web->submit();

            $query = "SELECT * FROM {{responses_$sid}}";
            $answers = $dbo->createCommand($query)->queryAll();

            $this->assertCount(1, $answers, 'Exactly one answer');
            $this->assertEquals('Y', $answers[0][$sgqa . '_S' . $this->questions['SQ001']->qid], 'Checkbox is Y');
            $this->assertEmpty($answers[0][$sgqa . '_S' . $this->questions['SQ001']->qid . '_Ccomment'], 'No comment');

            // Check db
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'MultipleChoiceMandatoryWithComment');
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Never check the box, just write a comment. The box should check automatically.
     */
    public function testOnlyComment()
    {
        $web  = self::$webDriver;
        list($sgqa, $subquestions) = $this->getSgqa();
        $url = $this->getSurveyUrl();
        $sid = self::$testSurvey->sid;
        $dbo = \Yii::app()->getDb();

        try {
            $web->get($url);

            $web->answerTextQuestion($sgqa . '_S' . $this->questions['SQ001']->qid . '_Ccomment', 'some comment');

            $web->submit();

            $query = "SELECT * FROM {{responses_$sid}}";
            $answers = $dbo->createCommand($query)->queryAll();

            $this->assertCount(1, $answers, 'Exactly one answer');
            $this->assertEquals('Y', $answers[0][$sgqa . '_S' . $this->questions['SQ001']->qid], 'Checkbox is Y');
            $this->assertEquals('some comment', $answers[0][$sgqa . '_S' . $this->questions['SQ001']->qid . '_Ccomment'], 'No comment');

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'MultipleChoiceMandatoryWithComment');
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Test so that the mandatory warning pops up.
     */
    public function testAbuseMandatory()
    {
        $web  = self::$webDriver;
        list($sgqa, $subquestions) = $this->getSgqa();
        $url = $this->getSurveyUrl();
        $sid = self::$testSurvey->sid;
        $dbo = \Yii::app()->getDb();

        try {
            self::$webDriver->get($url);

            // Write a comment.
            $web->answerTextQuestion($sgqa . '_S' . $this->questions['SQ001']->qid . '_Ccomment', 'some comment');

            // Unclick "First".
            $label = $web->findElement(WebDriverBy::id('label-answer' . $sgqa . '_S' . $this->questions['SQ001']->qid));
            $label->click();

            // Check so that comment is empty.
            $commentField = $web->findElement(WebDriverBy::id('answer' . $sgqa . '_S' . $this->questions['SQ001']->qid . '_Ccomment'));
            $comment = $commentField->getText();
            $this->assertEmpty($comment);

            $web->submit();

            // Sleep so modal has time to fade in.
            sleep(1);

            // Get alert box.
            $modal = $web->findElement(WebDriverBy::id('bootstrap-alert-box-modal'));
            $warningMessage = $modal->getText();
            $this->assertNotEmpty($warningMessage, 'There is a mandatory warning message');

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'MultipleChoiceMandatoryWithComment');
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * @return array
     */
    protected function getSgqa()
    {
        // Get questions.
        // TODO: Use \createFieldMap instead?
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }
        $subquestions = [];
        foreach ($questions['q1']->subquestions as $subq) {
            $subquestions[$subq->title] = $subq;
        }
        $sgqa = 'Q' . $questions['q1']->qid;
        return [$sgqa, $subquestions];
    }
}
