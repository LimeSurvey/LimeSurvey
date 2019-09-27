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
    private $_urlMan;
    private const HTTP_STRING = 'http://';
    private const INDEX_SITE = '/index.php';

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
            $survey->deleteSurvey($survey->sid,true);
        }
    }

    /**
     * Login, create survey, add group and question,
     * activate survey, execute survey, check database
     * result.
     */
    public function testCreateSurvey()
    {
        $this->markTestIncomplete();
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
            $title = self::$webDriver->findElement(WebDriverBy::id('surveyTitle'));
            $title->clear()->sendKeys('test survey 1');

            // Click save.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-form-button'));
            $save->click();

            sleep(5);

            // Go to structure sidebar
            $selectStructureSidebar = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('adminsidepanel__sidebar--selectorStructureButton')      
                )
            );

            $selectStructureSidebar->click();

            // Click "Add group".
            $addgroup = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('adminsidepanel__sidebar--selectorCreateQuestionGroup')
                )
            );
            $addgroup->click();

            // Fill in group title.
            $groupname = self::$webDriver->findElement(WebDriverBy::className('group-title'));
            $groupname->clear()->sendKeys('group1');

            // Click save and add question.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-and-close-button'));
            $save->click();
            sleep(1);

            // Create question.
            $save = self::$webDriver->findElement(WebDriverBy::id('adminsidepanel__sidebar--selectorCreateQuestion'));
            $save->click();
            sleep(1);

            // Add question title.
            $groupname = self::$webDriver->findElement(WebDriverBy::id('questionCode'));
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

            sleep(1);

            // Confirm.
            $overview = self::$webDriver->findElement(WebDriverBy::id('activateSurvey__basicSettings--proceed'));
            $overview->click();

            sleep(1);

            // Click "Overview".
            $overview = self::$webDriver->findElement(WebDriverBy::id('sidemenu_overview'));
            $overview->click();

            sleep(1);

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

    /**
     * This Test will check if its possible to view the main page.
     * 
     * @test
     * @return object
     */
    public function goToMainPage() 
    {   
        $adminurl = 'admin';
        $actualWebDriver = $this->_viewMainPage($adminurl);
        $this->assertNotNull($actualWebDriver, 'webdriver is null');
        return $actualWebDriver;
    }

    /**
     * This method will view the main page.
     * 
     * @param string $url URL
     * 
     * @return object
     */
    private function _viewMainPage(string $url) 
    {   
        $this->_urlMan = \Yii::app()->urlManager;
        $this->_urlMan->setBaseUrl(self::HTTP_STRING.self::$domain.self::INDEX_SITE);
        $url = $this->_urlMan->createUrl($url);
        return self::$webDriver->get($url);
    }

    /**
     * This test will click the welcome modal.
     * 
     * @return void
     *  
     * @test
     * @depends gotoMainPage
     */
    public function clickCloseButtonInWelcomeModal() 
    {
        try {
            //$adminurl = 'admin';
            $modal = 'welcomeModal';
            //$actualWebDriver = $this->_viewMainPage($adminurl);
            //$this->assertNotNull($actualWebDriver);
    
            //sleep(1);
            
            $actualClick = $this->_clickCloseButtonInModal($modal);
            $this->assertNotNull($actualClick, 'actualClick is null!');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
        
    }

    /**
     * This Test will click the close button inside the password warning modal view.
     * 
     * @test
     */
    public function clickCloseButtonInPasswordWarning() 
    {
        try {
            $adminurl = 'admin';
            $modal = 'admin-notification-modal';

            $actualWebDriver = $this->_viewMainPage($adminurl);
            $this->assertNotNull($actualWebDriver);
    
            sleep(1);
            
            $actualClick = $this->_clickCloseButtonInModal($modal);

            $this->assertNotNull($actualClick, 'actualClick is null!');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * This Method will do the click action inside the modal view.
     * 
     * @param string $modalname Name of the Modal 
     * 
     * @return object 
     */
    private function _clickCloseButtonInModal(string $modalname)
    {
        $modal = self::$webDriver->findElement(
            WebDriverBy::id($modalname)
        );
        $modalfooter = $modal->findElement(
            WebDriverBy::className('modal-footer')
        );
        $button = $modalfooter->findElement(
            WebDriverBy::className('btn btn-default')
        );
        return $button->click();
    }

    /**
     * This Test will create a survey.
     * 
     * @test 
     * @return void
     */
    public function clickOnCreateSurveyButton() 
    {
        try {
            // Before testing
            $adminurl = 'admin';
            $welcomeModal = 'welcomeModal';
            $passwordModal = 'admin-notification-modal';

            $actualWebDriver = $this->_viewMainPage($adminurl);
    
            sleep(1);

            $actualWebDriver = $this->_clickCloseButtonInModal($welcomeModal);

            sleep(1);

            $actualWebDriver = $this->_clickCloseButtonInModal($passwordModal);

            sleep(1);

            // Actual Test
            $actualWebDriver = $this->_clickOnCreateSurveyButton();
            $this->assertNotNull($actualWebDriver);
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * This method will click on CREATE SURVEY button.
     * 
     * @return object
     */
    private function _clickOnCreateSurveyButton() 
    {
        $elementName = 'panel-1';
        $createSurveyLink = self::$webDriver->findElement(
            WebDriverBy::id($elementName)
        );
        return $createSurveyLink->click();
    }
    /**
     * This test is filling the title and saves the survey.
     * 
     * @test 
     * @return void
     */
    public function fillInTitleAndSaveSurvey() 
    {   
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $actualWebDriver = $this->_viewMainPage($adminurl);
            $this->assertNotNull($actualWebDriver);

            sleep(1);

            $actualWebDriver = $this->_clickOnCreateSurveyButton();
            $this->assertNotNull($actualWebDriver);

            sleep(1);

            // Actual test
            $actualWebDriver = $this->_fillInTitleAndSave($title);
            $this->assertNotNull($actualWebDriver);
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }        
    } 

    /**
     * This method is filling the title input saves the survey.
     * 
     * @param string $title Title of the Survey
     * 
     * @return object 
     */
    private function _fillInTitleAndSave($title) 
    {
        $elementName = 'surveyTitle';
        $this->_fillInput($elementName, $title);
        return $this->_clickSave();
    }

    /**
     * This method is filling the current input field.
     * 
     * @param string $elementName Name of input field 
     * @param string $content     Content for input field
     * 
     * @return object $webDriver
     */
    private function _fillInput($elementName, $content) 
    {
        $input = self::$webDriver->findElement(WebDriverBy::id($elementName));
        $input->clear()->sendKeys($content);
        return $input;
    }

    /**
     * This method will click on save button.
     * 
     * @return object
     */
    private function _clickSave() 
    {
        $saveButtonName = 'save-form-button';
        $button = self::$webDriver->findElement(WebDriverBy::id($saveButtonName));
        return $button->click();
    }

    /**
     * This test will click on the structure tab inside the sidemenu.
     * 
     * @test
     * @return void
     */
    public function clickOnStructureButtonSidemenu() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $actualWebDriver = $this->_viewMainPage($adminurl);

            sleep(1);

            $actualWebDriver = $this->_clickOnCreateSurveyButton($actualWebDriver);

            sleep(1);

            $actualWebDriver = $this->_fillInTitleAndSave($actualWebDriver, $title);

            sleep(1);

            // Actual Test
            $actualWebDriver = $this->_clickOnStructureInSidemenu();
            $this->assertNotNull($actualWebDriver);
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * This test will try to click add group button.
     * 
     * @test 
     * @return void
     */
    public function clickAddGroup() 
    {   
        try  {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnStructureInSidemenu();

            // Actual Test 
            $actualWebDriver = $this->_clickOnAddGroupInsideSideMenu();
            $this->assertNotNull($actualWebDriver, 'actualWebDriver is null!');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * This method will click at STRUCTURE BUTTON inside sidemenu.
     * 
     * @return object
     */
    private function _clickOnStructureInSidemenu() 
    {
        $structure = 'adminsidepanel__sidebar--selectorStructureButton';
        $selectStructureSidebar = self::$webDriver->findElement(
            WebDriverBy::id($structure)     
        );

        return $selectStructureSidebar->click();
    }

    /**
     * This method will click at ADD GROUP inside sidemenu.
     * 
     * @return object
     */
    private function _clickOnAddGroupInsideSideMenu() 
    {
        $name = 'adminsidepanel__sidebar--selectorCreateQuestionGroup';
        $addGroupButton = self::$webDriver->findElement(
            WebDriverBy::id($name)
        );
        return $addGroupButton->click();
    }

    /**
     * This test will add an new question group to the survey.
     * 
     * @test
     * @return void
     */
    public function addGroup() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnStructureInSidemenu();

            sleep(1);

            $this->_clickOnAddGroupInsideSideMenu();

            sleep(1);

            // Actual Test 
            $actualWebDriver = $this->_fillGroupTitleAndSave();
            $this->assertNotNull($actualWebDriver);
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This Method will fill out the group title and saves it.
     * 
     * @return object
     */
    private function _fillGroupTitleAndSave() 
    {
        $this->_fillGroupTitle();
        return $this->_clickSaveaAndCloseButton();
    }

    /**
     * This method will fill the group title into input field.
     */
    private function _fillGroupTitle() 
    {
        $inputname = 'group-title';
        $title = 'Test Group Title 1';
        $input = self::$webDriver->findElement(
            WebDriverBy::className($inputname)
        );
        return $input->clear()->sendKeys($title);
    }

    /**
     * This method will click on save and close button. 
     * 
     * @return object
     */
    private function _clickSaveaAndCloseButton() 
    {
        $buttonName = 'save-and-close-button';
        $button = self::$webDriver->findElement(
            WebDriverBy::id($buttonName)
        );
        return $button->click();
    }

    /**
     * This test will check click at ADD QUESTION Button inside sidemenu.
     * 
     * @test 
     * @return void
     */
    public function clickAddQuestion() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnStructureInSidemenu();

            sleep(1);

            $this->_clickOnAddGroupInsideSideMenu();

            sleep(1);

            $this->_fillGroupTitleAndSave();

            sleep(1);
            
            // Actual Test 
            $actualWebDriver = $this->_clickOnAddQuestionInsideSidemenu();
            $this->assertNotNull($actualWebDriver, 'actualWebdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This method clicks at CREATE QUESTION BUTTON inside sidemenu.
     * 
     * @return object
     */
    private function _clickOnAddQuestionInsideSidemenu() 
    {
        $name = 'adminsidepanel__sidebar--selectorCreateQuestion';
        $button = self::$webDriver->findElement(
            WebDriverBy::className($name)
        );
        return $button->click();
    }

    /**
     * This test will try to add a question. 
     * 
     * @test 
     * @return void
     */
    public function addQuestion() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnStructureInSidemenu();

            sleep(1);

            $this->_clickOnAddGroupInsideSideMenu();

            sleep(1);

            $this->_fillGroupTitleAndSave();

            sleep(1);
            
            $this->_clickOnAddQuestionInsideSidemenu();

            // Actual Test 
            $actualWebDriver = $this->_fillQuestionAndSave('Test Question Title 01');
            $this->assertNotNull($actualWebDriver, 'actualWebDriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This method will fill question and save it.
     * 
     * @param string $content Content of Question
     * 
     * @return object
     */
    private function _fillQuestionAndSave(string $content)
    {
        $this->_fillQuestion($content);
        return $this->_clickSave();
    }

    /**
     * This Method will fill the question.
     * 
     * @param string $content Content of Question
     * 
     * @return object
     */
    private function _fillQuestion(string $content)
    {
        $name = 'questionCode';
        $input = self::$webDriver->findElement(
            WebDriverBy::id($name)
        );
        return $input->clear()->sendKeys($content);
    }

    /**
     * This method will click on Settings Button inside sidemenu.
     * 
     * @return object
     */
    private function _clickOnSettingsInSidemenu() 
    {
        $name = 'adminsidepanel__sidebar--selectorSettingsButton';
        $button = self::$webDriver->findElement(
            WebDriverby::id($name)
        );
        return $button->click();
    }

    /**
     * This test tries to click on Settings Button inside Sidemenu.
     * 
     * @test
     * @return void
     */
    public function clickOnSettingsButtonSidemenu()
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            // Actual Test
            $actualWebDriver = $this->_clickOnSettingsInSidemenu();
            $this->assertNotNull($actualWebDriver, 'actualWebdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This test tries to click at the overview tab inside the sidemenu.
     * 
     * @test 
     * @return void
     */
    public function clickOnOverViewTab()
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnSettingsInSidemenu();

            sleep(1);

            // Actual Test
            $actualWebDriver = $this->_clickOnOverviewTab();
            $this->assertNotNull($actualWebDriver, 'actualWebdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This method is clicking on overview tab inside settings tab (sidemenu).
     * 
     * @return object
     */
    private function _clickOnOverviewTab()
    {
        $name = 'sidemenu_overview';
        $button = self::$webDriver->findElement(
            WebDriverBy::id($name)
        );
        return $button->click();
    }

    /**
     * This test tries to activate a survey.
     * 
     * @test
     * @return void
     */
    public function activateSurvey() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnSettingsInSidemenu();

            sleep(1);

            $this->_clickOnOverviewTab();
            
            // Actual Test
            $actualWebDriver = $this->_activateSurvey();
            $this->assertNotNull($actualWebDriver, 'actualwebdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This method will click at ACTIVATE SURVEY Button 
     * and click at CONFIRM Button.
     * 
     * @return object
     */
    private function _activateSurvey() 
    {
        $this->_clickOnActivateSurveyButton();
        return $this->_clickConfirmActivateSurvey();
    }

    /**
     * This method will click at ACTIVATE SURVEY Button
     * 
     * @return object
     */
    private function _clickOnActivateSurveyButton() 
    {
        $name = 'ls-activate-survey';
        $button = self::$webDriver->findElement(
            WebDriverBy::id($name)
        );
        return $button->click();
    }

    /**
     * This method will at CONFIRM BUTTON inside 
     * ACTIVATE SURVEY.
     * 
     * @return object
     */
    private function _clickConfirmActivateSurvey()
    {
        $name = 'activateSurvey__basicSettings--proceed';
        $button = self::$webDriver->findElement(
            WebDriverby::id($name)
        );
        return $button->click();
    }

    /**
     * This test tries to execute the survey. 
     * 
     * @test 
     * @return void 
     */
    public function executeSurvey() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);

            sleep(1);

            $this->_clickOnCreateSurveyButton();

            sleep(1);

            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnSettingsInSidemenu();

            sleep(1);

            $this->_clickOnOverviewTab();
            
            // Actual Test 
            $actualWebDriver = $this->_executeSurvey();
            $this->assertNotNull($actualWebDriver, 'actualwebdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }   
    }

    /**
     * This method clicks at execute survey button.
     * 
     * @return object
     */
    private function _executeSurvey()
    {
        $text = 'Execute survey';
        $button = self::$webDriver->findElement(
            WebDriverBy::linkText($text)
        );
        return $button->click();
    }

    /**
     * This test is switching to a new tab.
     * 
     * @test 
     * @return void
     */
    public function switchToNewTab() 
    {
        try {
            // Before testing 
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);
            $this->_clickOnCreateSurveyButton();
            $this->_fillInTitleAndSave($title);

            sleep(1);

            $this->_clickOnSettingsInSidemenu();

            sleep(1);

            $this->_clickOnOverviewTab();
            $this->_activateSurvey();
            $this->_executeSurvey();

            // Actual Test 
            $actualWebDriver = $this->_switchToNewTab();
            $this->assertNotNull($actualWebDriver, 'webdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * This method will switch to a new tab.
     * 
     * @return void 
     */
    private function _switchToNewTab() 
    {
        $windowHandles = self::$webDriver->getWindowHandles();
        return self::$webDriver->switchTo()->window(
            end($windowHandles)
        );
    }

    /**
     * This test is creating a new survey, inserts title in it,
     * activate, and execute survey. also switching to new tab 
     * and click next button.
     * 
     * @test 
     * @return void
     */
    public function newTabActiveSurveyNextButton() {
        try {
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);
            $this->_clickOnCreateSurveyButton();
            $this->_fillInTitleAndSave($title);
            $this->_clickOnSettingsInSidemenu();
            $this->_clickOnOverviewTab();
            $this->_activateSurvey();
            $this->_executeSurvey();
            $this->_switchToNewTab();
            
            $actualWebDriver = $this->_clickNextButton();
            $this->assertNotNull($actualWebDriver, 'webdriver null');
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * This method clicks NEXT BUTTON.
     * 
     * @return object
     */
    private function _clickNextButton() 
    {
        $name = 'ls-button-submit';
        $button = self::$webDriver->findElement(
            WebDriverBy::id($name)
        );
        return $button->click();
    }

    /**
     * Test is checking questions.
     * 
     * @test 
     * @return void
     */
    public function getQuestions() 
    {
        try {
            $adminurl = 'admin';
            $title    = 'Test Survey 01';

            $this->_viewMainPage($adminurl);
            $this->_clickOnCreateSurveyButton();
            $this->_fillInTitleAndSave($title);
            $this->_clickOnSettingsInSidemenu();
            $this->_clickOnOverviewTab();
            $this->_activateSurvey();
            $this->_executeSurvey();
            $this->_switchToNewTab();
            $this->_clickNextButton();
            
            $sids = $this->_getSurveyIDsByDB();
            $this->assertCount(1, $sids);

            $sid = $sids[0]['sid'];
            $survey = $this->_getSurvey($sid);
            $this->assertNotEmpty($survey);
            $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
            
            $actual = $this->_getQuestions($survey);
            $this->assertCount(1, $actual, 'We have exactly one question');
            $this->assertTrue(isset($actual['question1']), json_encode(array_keys($actual)));
        } catch (\Exception $exception) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '__' . __FUNCTION__);
        }
    }

    /**
     * Getter for Questions from Database.
     * 
     * @param \Survey $survey Survey 
     * 
     * @return \Question[]
     */
    private function _getQuestions(\Survey $survey) 
    {
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }
        return $questions;
    }

    /**
     * Getter Survey
     * 
     * @param int $surveyID Survey ID
     * 
     * @return \Survey
     */
    private function _getSurvey(int $surveyID) 
    {
        return \Survey::model()->findByPk($surveyID);
    }
    /**
     * Getter SurveyIDs by DB
     * 
     * @return array
     */
    private function _getSurveyIDsByDB() 
    {
        $dbo = \Yii::app()->getDb();
        $query = 'SELECT sid FROM {{surveys}} ORDER BY datecreated DESC LIMIT 1';
        return $dbo->createCommand($query)->queryAll();
    }
}