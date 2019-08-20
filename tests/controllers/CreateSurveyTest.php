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
 * Login and create a survey, add a group
 * and a question.
 * @since 2017-11-17
 * @group createsurvey
 */
class CreateSurveyTest extends TestBaseClassWeb
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
        $criteria = new \CDbCriteria;
        $criteria->compare('correct_relation_defaultlanguage.surveyls_title', 'test survey 1', true, 'AND');
        $criteria->with = ['correct_relation_defaultlanguage'];
        $survey = \Survey::model()->find($criteria);
        if ($survey) {
            $survey->deleteSurvey($survey->sid);
        }
    }

    /**
     * Login, create survey, add group and question,
     * activate survey, execute survey, check database
     * result.
     */
    public function testCreateSurvey()
    {
        try {
            // Go to main page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl('admin');
            self::$webDriver->get($url);

            sleep(1);

            // Ignore welcome modal.
            try {
                $button = self::$webDriver->wait(1)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#welcomeModal button.btn-default')
                    )
                );
                $button->click();
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            } catch (TimeOutException $ex) {
                // Do nothing.
            }

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

            // Click on big "Create survey" button.
            $link = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#panel-1[data-url]')
                )
            );
            $link->click();

            // Fill in title.
            $title = self::$webDriver->findElement(WebDriverBy::id('surveyls_title'));
            $title->clear()->sendKeys('test survey 1');

            // Click save.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-form-button'));
            $save->click();

            sleep(2);

            // Remove notification.
            // TODO: Since 2018-06-18, this does not longer work. "Cannot scroll into view". Could be
            // a bug in Firefox, geckodriver, selenium or webdriver.
            //$save = self::$webDriver->findElement(WebDriverBy::cssSelector('button.close.limebutton'));
            //$save->click();
            //sleep(1);

            // Go to structure sidebar
            $selectStructureSidebar = self::$webDriver->findElement(WebDriverBy::id('adminsidepanel__sidebar--selectorStructureButton'));
            $selectStructureSidebar->click();
            

            // Click "Add group".
            $addgroup = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('adminsidepanel__sidebar--selectorCreateQuestionGroup')
                )
            );
            $addgroup->click();

            // Fill in group title.
            $groupname = self::$webDriver->findElement(WebDriverBy::id('group_name_en'));
            $groupname->clear()->sendKeys('group1');

            sleep(1);

            // Click save and add question.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-and-new-question-button'));
            $save->click();
            sleep(3);

            // Close question type selector
            try {
                $button = self::$webDriver->wait(2)->until(
                    WebDriverExpectedCondition::elementToBeClickable(
                        WebDriverBy::cssSelector('#selector__questionTypeSelector-modal button.btn-default')
                    )
                );
                $button->click();
            } catch (TimeOutException $ex) {
                // Do nothing.
            } catch (NoSuchElementException $ex) {
                // Do nothing.
            }
            sleep(1);

            // Add question title.
            $groupname = self::$webDriver->findElement(WebDriverBy::id('title'));
            $groupname->clear()->sendKeys('question1');

            // Click save.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-button'));
            $save->click();

            sleep(1);
            
            $selectSettingsSidebar = self::$webDriver->findElement(WebDriverBy::id('adminsidepanel__sidebar--selectorSettingsButton'));
            $selectSettingsSidebar->click();

            // Click "Overview".
            $overview = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('sidemenu_overview')
                )
            );
            $overview->click();

            sleep(2);

            // Click "Activate survey".
            $overview = self::$webDriver->findElement(WebDriverBy::id('ls-activate-survey'));
            $overview->click();

            // Confirm.
            $overview = self::$webDriver->findElement(WebDriverBy::id('activateSurvey__basicSettings--proceed'));
            $overview->click();

            sleep(1);

            // Click "Overview".
            $overview = self::$webDriver->findElement(WebDriverBy::id('sidemenu_overview'));
            $overview->click();

            sleep(2);

            // Click "Execute survey".
            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::linkText('Execute survey')
                )
            );
            $execute->click();

            sleep(1);

            // Switch to new tab.
            $windowHandles = self::$webDriver->getWindowHandles();
            self::$webDriver->switchTo()->window(
                end($windowHandles)
            );

            sleep(1);

            // New tab with active survey.
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            // Get questions.
            $dbo = \Yii::app()->getDb();
            $query = 'SELECT sid FROM {{surveys}} ORDER BY datecreated DESC LIMIT 1';
            $sids = $dbo->createCommand($query)->queryAll();
            $this->assertCount(1, $sids);
            $sid = $sids[0]['sid'];
            $survey = \Survey::model()->findByPk($sid);
            $this->assertNotEmpty($survey);
            $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
            $questionObjects = $survey->groups[0]->questions;
            $questions = [];
            foreach ($questionObjects as $q) {
                $questions[$q->title] = $q;
            }
            $this->assertCount(1, $questions, 'We have exactly one question');
            $this->assertTrue(isset($questions['question1']), json_encode(array_keys($questions)));

            // Enter answer text.
            $sgqa = $sid . 'X' . $survey->groups[0]->gid . 'X' . $questions['question1']->qid;
            $question = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $question->sendKeys('foo bar');

            sleep(1);

            // Click submit.
            $submitButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submitButton->click();

            // Check so that we see end page.
            $completed = self::$webDriver->findElement(WebDriverBy::cssSelector('div.completed-text'));
            $this->assertEquals(
                $completed->getText(),
                "Thank you!\nYour survey responses have been recorded.",
                'I can see completed text'
            );

            // Check so that response is recorded in database.
            $query = sprintf(
                'SELECT * FROM {{survey_%d}}',
                $sid
            );
            $result = $dbo->createCommand($query)->queryAll();
            $this->assertCount(1, $result, 'Exactly one response');
            $this->assertEquals('foo bar', $result[0][$sgqa], '"foo bar" response');

            // Switch to first window.
            $windowHandles = self::$webDriver->getWindowHandles();
            self::$webDriver->switchTo()->window(
                reset($windowHandles)
            );

            // Delete survey.
            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('ls-tools-button')
                )
            );
            $execute->click();
            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#ls-tools-button + ul li:first-child')
                )
            );
            $execute->click();
            $execute = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('input[type="submit"]')
                )
            );
            $execute->click();

            sleep(1);

            // Make sure the survey can't be found.
            $query = 'SELECT sid FROM {{surveys}} WHERE sid = ' . $sid;
            $sids = $dbo->createCommand($query)->queryAll();
            $this->assertCount(0, $sids);

        } catch (NoSuchElementException $ex) {
            // TODO :Duplicated code.
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        } catch (StaleElementReferenceException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        } catch (UnknownServerException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        } catch (TimeOutException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        } catch (ElementNotVisibleException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
