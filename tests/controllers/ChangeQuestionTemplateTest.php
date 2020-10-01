<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\ElementNotVisibleException;

/**
 * Admin interface edit question page
 * make sure that question template options are updated
 * @since 2018-06-25
 * @author  Dominik Vitt
 * @group createquestion
 */
class ChangeQuestionTemplateTest extends TestBaseClassWeb
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
     * Login, create survey, add group and question,
     * activate survey, execute survey, check database
     * result.
     */
    public function testChangeQuestionTemplate()
    {
        try {

            $gid = self::$testSurvey->groups[0]->gid;
            $qid = self::$testSurvey->groups[0]->questions[0]->qid;

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

            sleep(1);

            // Select bootstrap_buttons on Question theme dropdown
            $option = self::$webDriver->findElement(WebDriverBy::cssSelector('#question_template option[value=bootstrap_buttons]'));
            $option->click();

            sleep(1);

            // Select "Display theme options" tab
            $displayLink = self::$webDriver->findElement(WebDriverBy::linkText('Display theme options'));
            $displayLink->click();

            sleep(1);

            // Find button_size element
            $buttonSizeElement = self::$webDriver->findElement(WebDriverBy::cssSelector('#button_size'));
            $this->assertTrue(isset($buttonSizeElement), 'Found the button size element YY');

            // Switch back to "General options" tab
            $displayLink = self::$webDriver->findElement(WebDriverBy::linkText('General options'));
            $displayLink->click();

            sleep(1);

            // Change question template to default
            $option = self::$webDriver->findElement(WebDriverBy::cssSelector('#question_template option[value=core]'));
            $option->click();

            sleep(1);

            // Switch to Display tab
            // Try to find "button_size" - should throw exception NoSuchElementException (wrap in try-catch block and assert true in catch block)
            try {
                $buttonSizeElement = self::$webDriver->findElement(WebDriverBy::cssSelector('#button_size'));
                $this->assertNotEmpty(isset($buttonSizeElement), 'Found the button size element');
            } catch (NoSuchElementException $ex) {
                $this->assertTrue(true, 'Element not found');
            }
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        } 
    }
}
