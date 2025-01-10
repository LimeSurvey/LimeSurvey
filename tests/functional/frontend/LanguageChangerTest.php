<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2018-01-17
 * @group langchang
 */
class LanguageChangerTest extends TestBaseClassWeb
{
    /**
     * Setup before class.
     */
    public static function setupBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_143933.lss';
        self::importSurvey($surveyFile);

        // Activate survey.
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * 
     */
    public function testBasic()
    {
        // To make writing shorter.
        $web = self::$webDriver;
        $sgqa = $this->getSgqa();
        $url = $this->getSurveyUrl('pt');

        try {
            // Open survey.
            $web->get($url);

            // Dump for debugging.
            //$web->dumpBody();

            // Change to Deutsch.
            $web->changeLanguageSelect('de');

            // Dump for debugging.
            //$web->dumpBody();

            sleep(2);

            // Check so that we see German text.
            $text = $web->findElement(
                WebDriverBy::cssSelector('.question-count-text')
            );
            $this->assertStringContainsString($text->getText(), 'In dieser Umfrage sind 2 Fragen enthalten.');

            // Click next.
            $web->next();

            sleep(1);

            // Fill in first question.
            $web->answerTextQuestion($sgqa, 'This is an answer');

            // Change to English.
            $web->changeLanguage('en');

            sleep(1);

            // Go to second question group.
            $web->next();

            // Submit survey.
            $web->next();

            $query = sprintf(
                'SELECT * FROM {{responses_%d}}',
                self::$surveyId
            );
            $db = \Yii::app()->getDb();
            $rows = $db->createCommand($query)->queryAll();
            $this->assertCount(1, $rows);
            $this->assertEquals($rows[0][$sgqa], 'This is an answer');

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'LanguageChangerTest');
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }

        // Change language
        // Check so that text is still present.
        // Next and submit
        // Check database values.
    }

    /**
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
        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['q1']->qid;
        return $sgqa;
    }
}
