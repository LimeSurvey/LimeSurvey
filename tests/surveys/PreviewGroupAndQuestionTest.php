<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;
/**
 * @since 2019-07-28
 * @group preview
 */
class PreviewGroupAndQuestionTest extends TestBaseClassWeb
{

    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_previewGroupQuestion.lss';
        self::importSurvey($surveyFile);
        parent::setUpBeforeClass();
    }

    public function testPreview()
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questions = [];
        foreach($survey->groups as $group) {
            $questionObjects = $group->questions;
            foreach ($questionObjects as $q) {
                $questions[$q->title] = $q;
            }
        }
        /* Preview group with G2Q01 and set Q02=Y*/
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'action' => 'previewgroup',
                'gid' => $questions['G2Q01']['gid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have group 1 ? */
            $group = self::$webDriver->findElement(WebDriverBy::id('group-1'));
            $this->assertCount(1, $group,'Group not found');
            /* Check if 1st question in group is visible */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q01']['qid'])).isDisplayed());
            /* Check if 2nd question in group is not visible */
            $this->assertFalse(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q02']['qid'])).isDisplayed());
            /* Check if 3nd question in group is not visible */
            $this->assertFalse(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q03']['qid'])).isDisplayed());
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/PreviewGroupAndQuestionTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
