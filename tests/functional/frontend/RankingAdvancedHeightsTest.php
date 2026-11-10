<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverDimension;

/**
 * Test for issue #20781: Advanced ranking question caused horizontal scrolling after the screen got narrower,
 * and ignored the "Same height for all answer options" and "Same height for lists" settings.
 *
 * @group question
 */
class RankingAdvancedHeightsTest extends TestBaseClassWeb
{
    /**
     * Import and activate the survey with two advanced ranking questions (list view):
     * RankSame has both height settings on, RankFree has both off.
     */
    public static function setupBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_718245_ranking_advanced_heights.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Open the survey on a wide window, then make the window narrow (like rotating a phone)
     * and check the heights and the page width after each step.
     */
    public function testHeightsAndReflowAfterResize()
    {
        $web = self::$webDriver;
        $questions = [];
        foreach (\Question::model()->findAll("sid = :sid AND parent_qid = 0", [":sid" => self::$surveyId]) as $question) {
            $questions[$question->title] = $question->qid;
        }

        try {
            $web->manage()->window()->setSize(new WebDriverDimension(1280, 1024));
            $web->get($this->getSurveyUrl());
            $web->findById('sortable-choice-' . $questions['RankSame']);
            sleep(1);
            $this->assertSameHeights($this->getLayout($questions['RankSame']), 'wide window');

            $web->manage()->window()->setSize(new WebDriverDimension(360, 800));
            sleep(1);
            $layout = $this->getLayout($questions['RankSame']);
            $this->assertLessThanOrEqual(
                $layout['clientWidth'],
                $layout['scrollWidth'],
                'The page must not scroll horizontally after the window got narrower'
            );
            $this->assertSameHeights($layout, 'narrow window');

            $layout = $this->getLayout($questions['RankFree']);
            $this->assertSame([''], array_values(array_unique($layout['choiceMinHeights'])), 'No answer option height may be set when "Same height for all answer options" is off');
            $this->assertSame(['', ''], $layout['listMinHeights'], 'No list height may be set when "Same height for lists" is off');
            $this->assertGreaterThan(1, count(array_unique($layout['choiceHeights'])), 'The long answer option must be higher than the short ones');
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Assert that all answer options have the same height and both lists have the same height
     * @param array $layout From getLayout()
     * @param string $context Shown in the failure message
     * @return void
     */
    private function assertSameHeights(array $layout, $context)
    {
        $this->assertCount(1, array_unique($layout['choiceHeights']), "All answer options must have the same height ($context)");
        $this->assertCount(1, array_unique($layout['listHeights']), "Both lists must have the same height ($context)");
    }

    /**
     * Get the rendered heights of the answer options and lists of a ranking question, and the page width
     * @param integer $qid
     * @return array
     */
    private function getLayout($qid)
    {
        return self::$webDriver->executeScript(
            "var qid = arguments[0],
                choices = Array.from(document.querySelectorAll('#question' + qid + ' .sortable-item.ls-choice')),
                lists = [document.getElementById('sortable-choice-' + qid), document.getElementById('sortable-rank-' + qid)],
                height = function (element) { return Math.round(element.getBoundingClientRect().height); };
            return {
                scrollWidth: document.documentElement.scrollWidth,
                clientWidth: document.documentElement.clientWidth,
                choiceHeights: choices.map(height),
                choiceMinHeights: choices.map(function (element) { return element.style.minHeight; }),
                listHeights: lists.map(height),
                listMinHeights: lists.map(function (element) { return element.style.minHeight; })
            };",
            [$qid]
        );
    }
}
