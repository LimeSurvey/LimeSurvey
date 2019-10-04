<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * Test for issue #15348: Ranking question does not save answers when array filter and max columns are used.
 */
class RankingArrayFilterMaxColumnTest extends TestBaseClassWeb
{
    /**
     * Setup before class.
     */
    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_157447_array_filter_ranking_max_columns.lss';
        self::importSurvey($surveyFile);

        // Activate survey.
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * @return void
     * @todo
     */
    public function testRanking()
    {
        $this->markTestSkipped();

        /** @var string */
        $url = $this->getSurveyUrl();

        /** @var WebDriver */
        $web = self::$webDriver;

        /** @var Survey */
        $survey = \Survey::model()->findByPk(self::$surveyId);

        /** @var Question[] */
        $subQuestions = $survey->groups[0]->questions[0]->subquestions;
        /** @var Question[] */
        $subQuestionsByTitle = [];
        foreach ($subQuestions as $q) {
            $subQuestionsByTitle[$q->title] = $q;
        }

        try {
            // Open survey.
            $web->get($url);

            // Click next.
            $web->next();

            /** @var string Answer id to first subquestion. */
            $answerId = 'javatbd'
                . self::$surveyId
                . 'X' . $survey->groups[0]->gid
                . 'X'
                . $survey->groups[0]->questions[0]->qid
                . '1';

            // Click it.
            /** @var RemoteWebElement */
            $label = $web->findByCss('#' . $answerId . ' label');
            $label->click();

            /** @var string Answer id to second subquestion. */
            $answerId = 'javatbd'
                . self::$surveyId
                . 'X' . $survey->groups[0]->gid
                . 'X'
                . $survey->groups[0]->questions[0]->qid
                . '2';

            // Click it.
            /** @var RemoteWebElement */
            $label = $web->findByCss('#' . $answerId . ' label');
            $label->click();

            /** @var string Answer id to third subquestion. */
            $answerId = 'javatbd'
                . self::$surveyId
                . 'X' . $survey->groups[0]->gid
                . 'X'
                . $survey->groups[0]->questions[0]->qid
                . '3';

            // Click it.
            /** @var RemoteWebElement */
            $label = $web->findByCss('#' . $answerId . ' label');
            $label->click();

            // Go to next page.
            $web->next();
            sleep(1);

            /** @var string List item id to first answer option. */
            $liId = 'javatbd'
                . self::$surveyId
                . 'X' . $survey->groups[1]->gid
                . 'X'
                . $survey->groups[1]->questions[0]->qid
                . '1';

            /** @var RemoteWebElement */
            $li = $web->findById($liId);
            $dropZone = $web->findById('sortable-rank-' . $survey->groups[1]->questions[0]->qid);

            // TODO: Can't use mouse with geckodriver and Selenium?
            sleep(1);
            //$web->getMouse()->mouseMove($li->getCoordinates());
            //$web->action()->moveToElement($li)->perform();
            //$web
                //->action()
                //->moveToElement($li)
                //->clickAndHold($li)
                //->moveToElement($dropZone)
                //->release($dropZone)
                //->perform();

            //$web->getMouse()
                //->mouseDown($li->getCoordinates())
                //->mouseMove($dropZone->getCoordinates())
                //->mouseUp($dropZone->getCoordinates());
            sleep(3);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
        // fill in first question
        // fill in second question
        // submit
        // check database result
    }
}
