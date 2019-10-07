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
        /** @var string */
        $url = $this->getSurveyUrl();

        /** @var WebDriver */
        $web = self::$webDriver;

        /** @var Survey */
        $survey = \Survey::model()->findByPk(self::$surveyId);

        try {
            // Open survey.
            $web->get($url);

            // Click next.
            $web->next();

            /** @var string Answer id to first subquestion. */
            $answerId = $this->getAnswerId($survey) . '1';

            // Click it.
            /** @var RemoteWebElement */
            $label = $web->findByCss('#' . $answerId . ' label');
            $label->click();

            /** @var string Answer id to second subquestion. */
            $answerId = $this->getAnswerId($survey) . '2';

            // Click it.
            /** @var RemoteWebElement */
            $label = $web->findByCss('#' . $answerId . ' label');
            $label->click();

            /** @var string Answer id to third subquestion. */
            $answerId = $this->getAnswerId($survey) . '3';

            // Click it.
            /** @var RemoteWebElement */
            $label = $web->findByCss('#' . $answerId . ' label');
            $label->click();

            // Go to next page.
            $web->next();
            sleep(1);

            /** @var string List item id to first answer option. */
            $sgqa1 = $this->getItemListId($survey) . '1';

            // TODO: Can't use mouse with geckodriver and Selenium?
            sleep(1);
            // NB: Couldn't get mouse to work, use JS to simulate double click.
            /** @var string */
            $javascript = $this->getJavascriptDoubleClick($sgqa1);
            /** @var string */
            $result = $web->executeAsyncScript($javascript, []);
            $this->assertEquals('done', $result);
            sleep(1);

            /** @var string List item id to second answer option. */
            $sgqa2 = $this->getItemListId($survey) . '2';

            /** @var string */
            $javascript = $this->getJavascriptDoubleClick($sgqa2);
            /** @var string */
            $result = $web->executeAsyncScript($javascript, []);
            $this->assertEquals('done', $result);
            sleep(1);

            // Submit survey.
            $web->submit();

            // Check that answer was recorded correctly.
            /** @var string */
            $query = sprintf('SELECT * FROM {{survey_%d}}', $survey->sid);
            /** @var CDbConnection */
            $dbo = \Yii::app()->getDb();
            /** @var array */
            $answers = $dbo->createCommand($query)->queryAll();
            $this->assertCount(1, $answers);
            $this->assertEquals('1', $answers[0][$sgqa1]);
            $this->assertEquals('2', $answers[0][$sgqa2]);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Answer id to subquestion
     *
     * @param Survey $survey
     * @return string
     */
    protected function getAnswerId(\Survey $survey)
    {
        return 'javatbd'
            . self::$surveyId
            . 'X' . $survey->groups[0]->gid
            . 'X'
            . $survey->groups[0]->questions[0]->qid;
    }

    /**
     * @param Survey $survey
     * @return string
     */
    protected function getItemListId(\Survey $survey)
    {
        return self::$surveyId
            . 'X' . $survey->groups[1]->gid
            . 'X'
            . $survey->groups[1]->questions[0]->qid;
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getJavascriptDoubleClick($id)
    {
        return <<<END_JAVASCRIPT
var callback = arguments[arguments.length-1],
    event = new MouseEvent('dblclick', {
        'view': window,
        'bubbles': true,
        'cancelable': true
    });
document.getElementById('javatbd$id').dispatchEvent(event);
callback('done');
END_JAVASCRIPT;
    }
}
