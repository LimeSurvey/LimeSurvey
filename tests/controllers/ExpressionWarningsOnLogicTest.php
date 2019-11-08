<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;
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
    public static function setUpBeforeClass()
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

    /* Check with CheckInvalid*/
    public function testCheckInvalid()
    {
        $questions = $this->_getQuestions();
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
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-warning'))->isDisplayed(),"Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 1 warning.","Numbers of warning seems invalid, need one warning.");
        }  catch (NoSuchElementException $ex) {
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

    /* Check with CheckValidString*/
    public function testCheckValidString()
    {
        $questions = $this->_getQuestions();
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
            $this->assertTrue(self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-warning'))->isDisplayed(),"Unable to find the alert");
            /* We found the count of warnings */
            $elementStrong = self::$webDriver->findElement(WebDriverBy::cssSelector('#logicfiletable .alert-warning strong'));
            $strongAlert = $elementStrong->getText();
            $this->assertEquals($strongAlert, "This question has at least 2 warnings.","Numbers of warning seems invalid, need 2 warnings.");
        }  catch (NoSuchElementException $ex) {
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
    private function _getQuestions()
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questions = [];
        foreach($survey->groups as $group) {
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
    public static function tearDownAfterClass()
    {
        $url = self::getUrl(['login', 'route'=>'authentication/sa/logout']);
        self::openView($url);
        parent::tearDownAfterClass();
    }

}
