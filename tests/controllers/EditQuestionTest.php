<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;

/**
 * Login and edit a question.
 * @since 2020-09-08
 * @group editquestion
 */
class EditQuestionTest extends TestBaseClassWeb
{
    /**
     *
     */
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
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

        // Import survey.
        $surveyFile =  'tests/data/surveys/limesurvey_survey_573386.lss';
        self::importSurvey($surveyFile);

        // Browser login.
        self::adminLogin($username, $password);
    }

    /**
     *
     */
    public static function teardownAfterClass()
    {
        parent::tearDownAfterClass();

        // Delete survey.
        if (self::$testSurvey) {
            self::$testSurvey->delete();
            // NB: Unset so static teardown won't find it.
            self::$testSurvey = null;
        }
    }

    /**
     * Login, create survey, add group and question,
     * activate survey, execute survey, check database
     * result.
     */
    public function testEditQuestionText()
    {
        try {
            $gid = self::$testSurvey->groups[0]->gid;
            $qid = self::$testSurvey->groups[0]->questions[0]->qid;

            $newText = 'TEST EDIT 1';

            // Get default editor mode
            $editorMode = \Yii::app()->getConfig('defaulthtmleditormode');

            // Go to edit question page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl('admin/questions', array('sa'=>'editquestion', 'surveyid'=>self::$testSurvey->sid, 'gid'=>$gid, 'qid'=>$qid));
            self::$webDriver->get($url);
            sleep(1);

            // Ignore password warning.
            try {
                $button = self::$webDriver->wait(1)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#admin-notification-modal button.btn-default')
                    )
                );
                $button->click();
            } catch (TimeOutException $ex) {
                // Do nothing.
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            }
            sleep(5);

            switch ($editorMode) {
                case 'inline':
                    $iframe = null;
                    $driver = self::$webDriver;
                    // Wait for question's CKEditor iframe
                    self::$webDriver->wait(10)->until(
                        function () use ($driver, &$iframe) {
                            $iframeDiv = $driver->findElement(WebDriverBy::id('cke_question_en'));
                            if (empty($iframeDiv)) return false;
                            $iframe = $iframeDiv->findElement(WebDriverBy::tagName('iframe'));
                            return !empty($iframe);
                        }
                    );
                    $this->assertNotEmpty($iframe);
                    // Switch to question's CKEditor iframe
                    self::$webDriver->switchTo()->frame($iframe);
                    // Edit the question text
                    $question = self::$webDriver->findElement(WebDriverBy::tagName('body'));
                    $question->clear()->sendKeys($newText);
                    // Switch back to main content
                    self::$webDriver->switchTo()->defaultContent();
                    break;
                case 'popup':
                default:
                    // Edit the question text
                    $question = self::$webDriver->findElement(WebDriverBy::cssSelector('#question_en'));
                    $question->clear()->sendKeys($newText);
            }

            // Click save.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-button'));
            $save->click();
            sleep(1);

            // Check the value in the DB
            $oQuestion = \Question::model()->findByPk([ 'qid' => $qid , 'language' => self::$testSurvey->language]);
            $this->assertNotEmpty($oQuestion);
            $this->assertEquals($newText, $oQuestion->question);

        } catch (Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}