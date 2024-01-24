<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2019-11-08
 * @author Denis Chenu
 * @group expression
 */
class ExpressionWarningsOnLogicTest extends TestBaseClassWeb
{

    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_SurveyLogicWarnings.lss';
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

    /**
     * Check with CheckInvalid : compare in intval VS forced string : then forced string comparaison
     * @return void
     **/
    public function testCheckInvalid()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckInvalid']['gid'],
                'qid' => $questions['CheckInvalid']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have thew warning alert */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning'))->isDisplayed(), "Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 1 warning.", "Numbers of warning seems invalid, need one warning.");
        } catch (Exception $ex) {
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
     * Check with CheckValidString : compare in forced string (with + "") VS forced string : then forced string comparaison
     * 2 warnings : one for + and one for compare
     * @return void
     **/
    public function testCheckValidString()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckValidString']['gid'],
                'qid' => $questions['CheckValidString']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have thew warning alert */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning'))->isDisplayed(), "Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 2 warnings.", "Numbers of warning seems invalid, need 2 warnings.");
        } catch (Exception $ex) {
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
     * Check with relevance : forced string
     * @return void
     **/
    public function testCheckOnRelevance()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckOnRelevance']['gid'],
                'qid' => $questions['CheckOnRelevance']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have thew warning alert */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning'))->isDisplayed(), "Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 1 warning.", "Numbers of warning seems invalid, need one warning.");
        } catch (Exception $ex) {
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
     * Check with SubQ relevance : forced string 2 times + one OK
     * @return void
     **/
    public function testCheckSubQRelevance()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckSubQRelevance']['gid'],
                'qid' => $questions['CheckSubQRelevance']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have thew warning alert */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning'))->isDisplayed(), "Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 2 warnings.", "Numbers of warning seems invalid, need 2 warnings.");
        } catch (Exception $ex) {
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
     * Check with assigment : just a warning
     * @return void
     **/
    public function testCheckAssigment()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckAssigment']['gid'],
                'qid' => $questions['CheckAssigment']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have thew warning alert */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning'))->isDisplayed(), "Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 1 warning.", "Numbers of warning seems invalid, need 1 warning.");
        } catch (Exception $ex) {
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
     * Check with answers text : 4 ways : ge,gt,le and lt
     * @return void
     **/
    public function testCheckAnswersText()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckAnswersText']['gid'],
                'qid' => $questions['CheckAnswersText']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have thew warning alert */
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning'))->isDisplayed(), "Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-filled-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 4 warnings.", "Numbers of warning seems invalid, need 4 warnings.");
        } catch (Exception $ex) {
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
     * No issue with a compare
     * @return void
     **/
    public function testCheckNoIssue()
    {
        $questions = $this->getQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'admin/expressions/sa/survey_logic_file',
            [
                'sid' => self::$surveyId,
                'gid' => $questions['CheckNoIssue']['gid'],
                'qid' => $questions['CheckNoIssue']['qid'],
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1);
            /* Did we have a warning alert */
            $findWarnings = self::$webDriver->findElements(WebDriverBy::cssSelector('.alert-filled-warning'));
            $this->assertCount(0, $findWarnings, 'There are a false warnings with a valid compare.');
        } catch (Exception $ex) {
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
     * Get all the questions and return array[]
     * Key is question code
     * Array return questions attributes
     * @return array[]
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
