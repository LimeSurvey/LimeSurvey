<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @group date
 */
class RankingFilterHideShowTest extends TestBaseClassWeb
{

    /**
     * Setup before class.
     */
    public static function setupBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Do the test
     */
    public function testRankingFilterShown()
    {
        /** @var string */
        $url = $this->getSurveyUrl();

        /** @var WebDriver */
        $web = self::$webDriver;
        // Get questions.
        $questionObjects = \Question::model()->findAll("sid = :sid AND parent_qid = 0",array(":sid"=>self::$surveyId));
        /** @var \Question[] */
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }
        try {
            $web->get($url); // Open survey.
            $web->next(); // Click next.
            sleep(1);
            /* Check if ranking question is totally hidden */
            /** @var RemoteWebElement */
            $rankingQuestion = self::$webDriver->findElement(WebDriverBy::id('question'.$questions['Rank']->qid));
            $this->assertFalse($rankingQuestion->isDisplayed());
            /* Click on 1st multiple choice : this must show ranking question */
            $checkboxSgqa = 'Q' .$questions['FILTER']->qid . '_17600';
            $labelCheckbox = self::$webDriver->findElement(
                WebDriverBy::cssSelector(
                    sprintf(
                        'label[for="%s"]',
                        'answer' . $checkboxSgqa
                    )
                )
            );
            $labelCheckbox->click();
            $this->assertTrue($rankingQuestion->isDisplayed());
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
