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
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_previewGroupQuestion.lss';
        self::importSurvey($surveyFile);
        /* Login */
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }
        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;
        // Browser login.
        self::adminLogin($username, $password);
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
        /* Preview group with G2Q01*/
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
            $this->assertTrue($group->isDisplayed());
            /* Check if 1st question in group is visible */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q01']['qid']))->isDisplayed());
            /* Check if 2nd question in group is not visible */
            $this->assertFalse(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q02']['qid']))->isDisplayed());
            /* Check if 3nd question in group is not visible */
            $this->assertFalse(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q03']['qid']))->isDisplayed());
            /* Check if 4th question in group is not visible */
            $this->assertFalse(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q04']['qid']))->isDisplayed());

            /* Check Y on question 1, and check if Q02 is visible */
            $yTextSGQA = self::$surveyId."X".$questions['G2Q01']['gid']."X".$questions['G2Q01']['qid'];
            $yText = self::$webDriver->findElement(WebDriverBy::id("answer".$yTextSGQA));
            $yText->sendKeys("Y");
            sleep(1);
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q04']['qid']))->isDisplayed(),"Javascript action in preview group broken");

        }  catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/PreviewGroupAndQuestionTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
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
                'Q02' => 'Y',
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Check if 3nd question in group is not visible */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q03']['qid']))->isDisplayed(),"Prefilling url broken when preview group");

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
        /* Preview question with G3Q02*/
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'action' => 'previewquestion',
                'gid' => $questions['G3Q02']['gid'],
                'qid' => $questions['G3Q02']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Check question is visble */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G3Q02']['qid']))->isDisplayed(),"Question preview force relevance broken");
            /* Check filter is done */
            $secondLineSGQA = self::$surveyId."X".$questions['G3Q02']['gid']."X".$questions['G3Q02']['qid']."SQ002";
            $this->assertFalse(self::$webDriver->findElement(WebDriverBy::id('javatbd'.$secondLineSGQA))->isDisplayed(),"Question preview force relevance broken");
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
        /* Preview question with G3Q02 and set Q03=Y*/
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'action' => 'previewquestion',
                'gid' => $questions['G3Q02']['gid'],
                'qid' => $questions['G3Q02']['qid'],
                'Q03' => "Y",
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Check question is visble */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G3Q02']['qid']))->isDisplayed(),"Question preview force relevance broken");
            /* Check filter is done */
            $secondLineSGQA = self::$surveyId."X".$questions['G3Q02']['gid']."X".$questions['G3Q02']['qid']."SQ002";
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::id('javatbd'.$secondLineSGQA))->isDisplayed(),"Question preview force relevance broken");
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
