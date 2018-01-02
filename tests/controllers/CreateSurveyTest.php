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
    /** @var \Survey $survey */
    private static $survey;

    /**
     *
     * @throws \Exception
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
     * @throws \CDbException
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
     * @throws \Exception
     */
    public function testCreateSurvey()
    {
        try {
            // Go to main page.
            $url = self::getUrl(['route'=>'']);
            self::$webDriver->get($url);

            // Ignore welcome modal.
            self::findAndClick(WebDriverBy::cssSelector('#welcomeModal button.btn-default'));

            // Ignore password warning.
            self::findAndClick(WebDriverBy::cssSelector('#admin-notification-modal button.btn-default'));

            // Click on big "Create survey" button.
            self::findAndClick(WebDriverBy::id('panel-1'),10);

            // Fill in title.
            $title = self::findAndClick(WebDriverBy::id('surveyls_title'));
            $title->clear()->sendKeys('test survey 1');

            // Click save.
            self::findAndClick(WebDriverBy::id('save-form-button'));

            // find & assert the survey summary page tag
            $element = $this->findViewTag('surveySummary');
            $this->assertNotEmpty($element);

            $sid = intval($element->getAttribute('model_id'));
            $survey = \Survey::model()->findByPk($sid);
            if($survey){
                self::$survey = $survey;
            }

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

    /**
     * @throws \Exception
     */
    public function testAddGroup(){

        self::openSurveySummary();

        // Click "Add group".
        self::findAndClick(WebDriverBy::cssSelector('#panel-1 .panel-body-link a'), 10);

        // Fill in group title.
        $groupname = self::findAndClick(WebDriverBy::id('group_name_en'));
        $groupname->clear()->sendKeys('group1');

        // Click save.
        self::findAndClick(WebDriverBy::id('save-button'));

        // find tag and assert
        $element = $this->findViewTag('editGroup');
        $this->assertNotEmpty($element);

        self::findAndClick(WebDriverBy::id('save-and-close-button'));
    }

    /**
     * @throws \Exception
     */
    public function testAddQuestion(){

        // Click "Add question".
        self::findAndClick(WebDriverBy::id('panel-1'));

        // Add question title.
        $questionCode = self::find(WebDriverBy::id('title'));
        $questionCode->sendKeys('Q1');

        // Click save.
        self::findAndClick(WebDriverBy::id('save-button'));

        // find tag and assert
        $element = $this->findViewTag('questionSummary');
        $this->assertNotEmpty($element);

    }

    /**
     * @throws \Exception
     */
    public function testActivateSurvey(){

        self::openSurveySummary();

        // Click "Activate survey".
        self::findAndClick(WebDriverBy::id('ls-activate-survey'),5);

        // Confirm.
        self::findAndClick(WebDriverBy::id('activateSurvey__basicSettings--proceed'));

        // find tag and assert
        self::openSurveySummary();
        $element = self::findAndClick(WebDriverBy::linkText('Stop this survey'));
        $this->assertNotEmpty($element);

    }

    /**
     * @throws \Exception
     */
    public function testExecuteSurvey(){

        self::openInterview();

        // we can see the next button
        $element = self::find(WebDriverBy::id('ls-button-submit'),5);
        $this->assertNotEmpty($element);
    }

    public function testSurveysCount(){

        // TODO is this OK?
        $dbo = \Yii::app()->getDb();
        $query = 'SELECT sid FROM {{surveys}} ORDER BY datecreated DESC LIMIT 1';
        $sids = $dbo->createCommand($query)->queryAll();
        $this->assertCount(1, $sids);
    }

    public function testInsertedSurveyGroupsCount(){
        $this->assertCount(1, self::$survey->groups, 'We have exactly one Group');
    }

    public function testInsertedSurveyQuestionsCount(){
        $this->assertCount(1, self::$survey->groups[0]->questions, 'We have exactly one question');
    }

    public function testAddedQuestionCode(){
        $group = self::$survey->groups[0];
        $question = $group->questions[0];
        self::assertEquals('Q1',$question->title);
    }

    /**
     * @throws \Exception
     */
    public function testInsertRecord(){

        self::openInterview();
        // skip welcome page
        self::findAndClick(WebDriverBy::id('ls-button-submit'),5);

        $survey = self::$survey;
        $group = self::$survey->groups[0];
        $question = $group->questions[0];

        // Enter answer text.
        $sgqa = $survey->sid . 'X' . $group->gid . 'X' . $question->qid;
        $element= self::find(WebDriverBy::id('answer' . $sgqa));
        $element->sendKeys('foo bar');

        // submit
        self::findAndClick(WebDriverBy::id('ls-button-submit'),5);

        // Check so that we see end page.
        $completed = self::find(WebDriverBy::cssSelector('div.completed-text'));
        $this->assertEquals(
            $completed->getText(),
            "Thank you!\nYour survey responses have been recorded.",
            'I can see completed text'
        );
    }

    /**
     * @throws \CException
     */
    public function testRecordedResponsesCount(){
        $responses = self::getRecordedResponses();
        $this->assertCount(1, $responses, 'Exactly one response');
    }

    /**
     * @throws \CException
     */
    public function testRecorderResponsevalue(){
        return;
        $responses = self::getRecordedResponses();
        $response = $responses[0];

        $survey = self::$survey;
        $group = self::$survey->groups[0];
        $question = $group->questions[0];

        // Enter answer text.
        $sgqa = $survey->sid . 'X' . $group->gid . 'X' . $question->qid;
        $this->assertEquals('foo bar', $response[$sgqa], '"foo bar" response');

    }


    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws \CException
     * @throws \Exception
     */
    public function testTheRest(){

        return;


        // Check so that response is recorded in database.

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

    }

    private function openSurveySummary(){
        $url = self::getUrl(['route'=>'survey/sa/view&surveyid='.self::$survey->primaryKey]);
        self::$webDriver->get($url);
    }

    /**
     * @throws \Exception
     */
    private function openInterview(){
        self::openSurveySummary();

        self::findAndClick(WebDriverBy::linkText('Execute survey'));
        // Switch to new tab.
        $windowHandles = self::$webDriver->getWindowHandles();
        self::$webDriver->switchTo()->window(
            end($windowHandles)
        );

    }

    /**
     * @return array
     * @throws \CException
     */
    private function getRecordedResponses(){
        $dbo = \Yii::app()->getDb();
        $query = sprintf(
            'SELECT * FROM {{survey_%d}} WHERE submitdate IS NOT NULL',
            self::$survey->sid
        );
        return $dbo->createCommand($query)->queryAll();

    }


}
