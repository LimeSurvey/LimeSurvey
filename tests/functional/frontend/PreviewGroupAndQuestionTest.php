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
    public static function setUpBeforeClass(): void
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
        // Browser login.
        self::adminLogin($username, $password);
    }

    /* Preview group with G2Q01*/
    public function testPreviewGroup()
    {
        $web = self::$webDriver;

        $questions = $this->getQuestions();
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
            // TODO: This test fails when running DBENGINE=MYISAM and the following suit:
            // <testsuite name="Test">
            //   <file>tests/functional/backend/InstallationControllerTest.php</file>
            //   <file>tests/functional/frontend/MultipleChoiceNextPreviousTest.php</file>
            //   <file>tests/functional/frontend/PreviewGroupAndQuestionTest.php</file>
            // </testsuite>
            // TODO: Sometimes it's group-0 instead of group-1.
            //$group = self::$webDriver->findElement(WebDriverBy::id('group-1'));
            //$this->assertTrue($group->isDisplayed());

            /* Check if 1st question in group is visible */
            $this->assertTrue(self::$webDriver->findById('question'.$questions['G2Q01']['qid'])->isDisplayed());
            /* Check if 2nd question in group is not visible */
            $this->assertFalse(self::$webDriver->findById('question'.$questions['G2Q02']['qid'])->isDisplayed());
            /* Check if 3nd question in group is not visible */
            $this->assertFalse(self::$webDriver->findById('question'.$questions['G2Q03']['qid'])->isDisplayed());
            /* Check if 4th question in group is not visible */
            $this->assertFalse(self::$webDriver->findById('question'.$questions['G2Q04']['qid'])->isDisplayed());

            /* Check Y on question 1, and check if Q02 is visible */
            $yTextSGQA = "Q".$questions['G2Q01']['qid'];
            $yText = self::$webDriver->findElement(WebDriverBy::id("answer".$yTextSGQA));
            $yText->sendKeys("Y");
            sleep(1);
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q04']['qid']))->isDisplayed(),
                "Javascript action in preview group broken"
            );
        } catch (NoSuchElementException $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($ex));
        }
    }
    /* Preview group with G2Q01 and prefill Q02=Y*/
    public function testPreviewGroupPrefill()
    {
        $questions = $this->getQuestions();
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
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G2Q03']['qid']))->isDisplayed(),
                "Prefilling url broken when preview group"
            );
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/'.__CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /* Preview question G3Q02*/
    public function testPreviewQuestion()
    {
        $questions = $this->getQuestions();
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
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G3Q02']['qid']))->isDisplayed(),
                "Question preview force relevance broken"
            );
            /* Check filter is done */
            $thirdLineSGQA = "Q".$questions['G3Q02']['qid']."SQ003";
            $this->assertFalse(
                self::$webDriver->findElement(WebDriverBy::id('javatbd'.$thirdLineSGQA))->isDisplayed(),
                "Question preview force relevance broken"
            );
            /* Check EM js */
            $checkboxToClickSGQA =
                "Q".$questions['G3Q02']['qid']."SQ001";
            $label = self::$webDriver->findElement(
                WebDriverBy::cssSelector(
                    sprintf(
                        'label[for="%s"]',
                        'answer' . $checkboxToClickSGQA
                    )
                )
            );
            $label->click();
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('javatbd'.$thirdLineSGQA))->isDisplayed(),
                "Question preview Expression javascript broken"
            );
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/'.__CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /* Preview question with G3Q02 and prefill Q03=Y*/
    public function testPreviewQuestionPrefill()
    {
        $questions = $this->getQuestions();
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
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('question'.$questions['G3Q02']['qid']))->isDisplayed(),
                "Question preview force relevance broken"
            );
            /* Check filter is done */
            $secondLineSGQA = "Q".$questions['G3Q02']['qid']."SQ002";
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('javatbd'.$secondLineSGQA))->isDisplayed(),
                "Question preview force relevance broken"
            );
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/'.__CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /**
     * @return Question[]
     */
    private function getQuestions()
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questions = [];
        foreach ($survey->groups as $group) {
            $questionObjects = $group->questions;
            foreach ($questionObjects as $q) {
                $questions[$q->title] = $q;
            }
        }
        return $questions;
    }

    /**
     * @inheritdoc
     * Log out to try to disable issue in Installer test
     */
    public static function tearDownAfterClass(): void
    {
        $url = self::getUrl(['login', 'route'=>'authentication/sa/logout']);
        self::openView($url);
        parent::tearDownAfterClass();
    }
}
