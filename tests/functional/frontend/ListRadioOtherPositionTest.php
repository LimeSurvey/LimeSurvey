<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2022-01-24
 * @group otheroption
 */
class ListRadioOtherPositionTest extends TestBaseClassWeb
{

    /**
     *
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile =  'tests/data/surveys/limesurvey_survey_767665_ListRadioOtherPositionTest.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Import and activate survey at every test.
     */
    public function setUp(): void
    {
        self::$testHelper->activateSurvey(self::$surveyId);
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
     * Test different "Other" option position
     */
    public function testOtherPositions()
    {
        // To make writing shorter.
        $web  = self::$webDriver;

        $url = $this->getSurveyUrl();
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questions = $survey->groups[0]->questions;

        try {
            self::$webDriver->get($url);

            // Check "At beginning" position
            $qid = $questions[0]->qid;
            $label = $web->findElement(WebDriverBy::cssSelector('#question' . $qid . ' ul.answers-list > li:first-child label'));
            $labelText = $label->getText();
            $this->assertEquals($labelText, 'Other:');

            // Check "After specific answer option" position
            $qid = $questions[1]->qid;
            $label = $web->findElement(WebDriverBy::cssSelector('#question' . $qid . ' ul.answers-list > li:nth-child(2) label'));
            $labelText = $label->getText();
            $this->assertEquals($labelText, 'Other:');

            // Check "At end" position
            $qid = $questions[2]->qid;
            $label = $web->findElement(WebDriverBy::cssSelector('#question' . $qid . ' ul.answers-list > li:last-child label'));
            $labelText = $label->getText();
            $this->assertEquals($labelText, 'Other:');

            // Check "Before No Answer" position
            $qid = $questions[3]->qid;
            $label = $web->findElement(WebDriverBy::cssSelector('#question' . $qid . ' ul.answers-list > li:nth-child(3) label'));
            $labelText = $label->getText();
            $this->assertEquals($labelText, 'Other:');

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
