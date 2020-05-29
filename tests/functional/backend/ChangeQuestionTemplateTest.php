<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\JavaScriptExecutor;
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
     * Setup
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
            $url = $urlMan->createUrl(
                'questionEditor/view',
                array( 'surveyid'=>self::$testSurvey->sid, 'gid'=>$gid, 'qid'=>$qid)
            );
            $web = self::$webDriver;
            $web->get($url);

            sleep(2);

            $web->dismissModal();
            $web->dismissModal();
            sleep(2);


            $oElementQuestionEditorButton = $this->waitForElementShim($web, '#questionEditorButton');
            $web->wait(20)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#questionEditorButton')));
            $oElementAdvancedOptionsPanel = $this->waitForElementShim($web, '#advanced-options-container');
            $web->wait(20)->until(WebDriverExpectedCondition::visibilityOf($oElementAdvancedOptionsPanel));
            
            sleep(1);
            $oElementQuestionEditorButton->click();
            
            
            //// Old way, by useing the html elements
            sleep(1);
            // // Select bootstrap_buttons on Question theme dropdown
            $option = $web->findElement(WebDriverBy::cssSelector('#question_template option[value=bootstrap_buttons]'));
            $option->click();
            sleep(5);

            //// New way by triggering a vuejs function
            //$web->executeScript('LS.EventBus.$emit("questionTypeChange", {type: "L", name: "bootstrap_buttons"})');
            $web->wait(20)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('advanced-options-container')));
            sleep(3);

            // Select "Display theme options" tab
            $displayLink = $web->findElement(WebDriverBy::linkText('Display theme options'));
            $displayLink->click();

            sleep(1);

            // Find button_size element
            $buttonSizeElement = $web->findById('input-button_size_0');
            $this->assertNotEmpty($buttonSizeElement, 'Found the button size element YY');

            // Switch back to "General options" tab
            $displayLink = $web->findElement(WebDriverBy::linkText('Display'));
            $displayLink->click();

            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('uncollapsed-general-settings')));
            // Change question template to default
            $option = $web->findElement(WebDriverBy::cssSelector('#question_template option[value=core]'));
            $option->click();

            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('advanced-options-container')));

            // Switch to Display tab
            // Try to find "button_size" - should throw exception NoSuchElementException
            // (wrap in try-catch block and assert true in catch block)
            try {
                $buttonSizeElement = $web->findById('input-button_size_0');
                $this->assertEmpty($buttonSizeElement, 'Found the button size element');
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

    /**
     * This Test is checking the question view.
     * @test
     */
    public function goToQuestionView()
    {
        try {
            $gid = self::$testSurvey->groups[0]->gid;
            $qid = self::$testSurvey->groups[0]->questions[0]->qid;
            
            // Go to edit question page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl('questionEditor/view', array('surveyid'=>self::$testSurvey->sid, 'gid'=>$gid, 'qid'=>$qid));
            $actualWebDriver = self::$webDriver->get($url);

            $this->assertNotNull($actualWebDriver, 'The WebDriver is null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($exception)
            );
        }
    }

    /**
     * This Method is testing if the Question Editor is clickable.
     *
     * @test
     */
    public function changeToQuestionEditorView()
    {
        try {
            $gid = self::$testSurvey->groups[0]->gid;
            $qid = self::$testSurvey->groups[0]->questions[0]->qid;

            // Go to edit question page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl(
                'questionEditor/view',
                array('surveyid'=>self::$testSurvey->sid, 'gid'=>$gid, 'qid'=>$qid)
            );
            $web = self::$webDriver;
            $web->get($url);

            sleep(3);

            // Select Question Editor View
            try {
                $questionEditorButton = $web->wait(5)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#questionEditorButton')
                    )
                );
                $questionEditorButton->click();

                // Check if General Settings Container is there
                $generalSettingsContainer = $web->findElement(
                    WebDriverBy::className('question-option-general-container')
                );
                $this->assertNotNull($generalSettingsContainer);
            } catch (TimeOutException $ex) {
                // Do nothing.
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            }
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($exception));
        }
    }

    /**
     * This Method is changing the question theme for the current question.
     * Also checking if the value is changed inside the database.
     *
     * TODO: This test will fail cause of bug.
     * TODO: Bug #15330.
     *
     * @test
     */
    public function selectQuestionThemeForQuestion()
    {
        try {
            $gid = self::$testSurvey->groups[0]->gid;
            $qid = self::$testSurvey->groups[0]->questions[0]->qid;

            // Go to edit question page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl(
                'questionEditor/view',
                array('surveyid'=>self::$testSurvey->sid, 'gid'=>$gid, 'qid'=>$qid)
            );
            $web = self::$webDriver;
            $web->get($url);

            sleep(5);

            // Select Question Editor View
            try {
                $questionEditorButton = $web->wait(5)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#questionEditorButton')
                    )
                );
                $questionEditorButton->click();

                // Check if General Settings Container is there
                $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id('uncollapsed-general-settings') ));
                $generalSettingsContainer = $web->findElement(
                    WebDriverBy::className('question-option-general-container')
                );
                $this->assertNotNull($generalSettingsContainer);
            } catch (TimeOutException $ex) {
                // Do nothing.
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            }

            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('uncollapsed-general-settings')));

            // Select new Question Theme for Question

            // Select bootstrap_buttons on Question theme dropdown
            $option = $web->findByCss('#question_template option[value=bootstrap_buttons]');
            $option->click();
            
            sleep(3);

            // Save Question
            $saveButton = $web->findElement(WebDriverBy::cssSelector('#save-button'));
            $saveButton->click();

            $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('uncollapsed-general-settings')));

             // Change question template to default
            $option = $web->findElement(WebDriverBy::cssSelector('#question_template option[value=core]'));
            $option->click();
 
            sleep(3);
            
            // Save Question
            $saveButton = $web->findElement(WebDriverBy::cssSelector('#save-button'));
            $saveButton->click();
            
            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id('advanced-options-container') ));

            // Check if Scope-apply-base-style exists
            $scopeApplyBaseStyleContainer = $web->findElement(
                WebDriverBy::className('scope-apply-base-style')
            );
            $this->assertNotNull($scopeApplyBaseStyleContainer);

            sleep(3);

            // Check if Display theme options link exists
            try {
                $displayLink = $web->findElement(WebDriverBy::linkText('Display theme options'));
            } catch (NoSuchElementException $ex) {
                $this->assertTrue(true, 'Element not found');
            }
            $this->assertEmpty($displayLink);
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(true, self::$testHelper->javaTrace($exception));
        }
    }
}
