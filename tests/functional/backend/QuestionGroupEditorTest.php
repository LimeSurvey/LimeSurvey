<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;

/**
 * Create and edit a question group.
 * @since 2020-09-08
 * @group editquestion
 */
class QuestionGroupEditorTest extends TestBaseClassWeb
{
    /**
     *
     */
    public static function setupBeforeClass(): void
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
        $surveyFile =  'tests/data/surveys/limesurvey_survey_143933.lss';
        self::importSurvey($surveyFile);

        // Browser login.
        self::adminLogin($username, $password);
    }

    /**
     *
     */
    public static function tearDownAfterClass(): void
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
     * Login, create group, check database result.
     */
    public function testCreateQuestionGroup()
    {
        try {
            // Go to add group page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl('questionGroupsAdministration/add', array('surveyid'=>self::$testSurvey->sid));
            self::$webDriver->get($url);

            $this->ignorePasswordWarning();
            $this->ignorePasswordWarning();

            // Edit group name in main language (English)
            $groupNameEnglish = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('group_name_en')));
            $groupNameEnglish->clear()->sendKeys("English name");

            // Edit description in main language
            $this->sendTextToEditor("description_en", "English description");

            // Edit randomization group
            $randomizationGroup = self::$webDriver->findElement(WebDriverBy::id('randomization_group'));
            $randomizationGroup->clear()->sendKeys("1");

            // Edit group relevance equation
            $groupRelevance = self::$webDriver->findElement(WebDriverBy::id('grelevance'));
            $groupRelevance->clear()->sendKeys("1");

            // Switch to German tab.
            self::$webDriver->executeScript("window.scrollTo(0, 0);");  // Scroll to top because otherwise the tabs may be hidden under the topbar
            sleep(2);
            // Make sure the language tabs are visible and click on German tab
            $germanTab = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::partialLinkText('German')
                )
            );
            $germanTab->click();

            // Wait a moment for tab content to load
            sleep(1);

            // Ensure the German input field is visible before interacting with it
            self::$webDriver->executeScript("document.getElementById('group_name_de').scrollIntoView(true);");

            // Edit group name in German
            $groupNameGerman = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('group_name_de')));
            $groupNameGerman->clear()->sendKeys("German name");

            // Edit description in German
            $this->sendTextToEditor("description_de", "German description");

            // Click save.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-button'));
            $save->click();

            // Check the value in the DB
            $oGroupsCriteria = new \CDbCriteria();
            $oGroupsCriteria->condition = "sid = :sid";
            $oGroupsCriteria->params = array(':sid' => self::$testSurvey->sid);
            $oGroupsCriteria->order = "group_order DESC";
            $oGroup = \QuestionGroup::model()->with('questiongroupl10ns')->find($oGroupsCriteria);

            $this->assertNotEmpty($oGroup);
            $this->assertEquals("English name", $oGroup->questiongroupl10ns['en']->group_name);
            $this->assertEquals("German name", $oGroup->questiongroupl10ns['de']->group_name);
            $this->assertEquals("English description", $oGroup->questiongroupl10ns['en']->description);
            $this->assertEquals("German description", $oGroup->questiongroupl10ns['de']->description);
            $this->assertEquals("1", $oGroup->randomization_group);
            $this->assertEquals("1", $oGroup->grelevance);

        } catch (Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Login, edit group, check database result.
     */
    public function testEditQuestionGroup()
    {
        try {
            $gid = self::$testSurvey->groups[0]->gid;

            // Go to edit group page.
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl('questionGroupsAdministration/edit', array('surveyid'=>self::$testSurvey->sid, 'gid'=>$gid));
            self::$webDriver->get($url);

            $this->ignorePasswordWarning();
            $this->ignorePasswordWarning();

            // Edit group name in main language (English)
            $groupNameEnglish = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('group_name_en')));
            $groupNameEnglish->clear()->sendKeys("Edited English name");

            // Edit description in main language
            $this->sendTextToEditor("description_en", "Edited English description");

            // Edit randomization group
            $randomizationGroup = self::$webDriver->findElement(WebDriverBy::id('randomization_group'));
            $randomizationGroup->clear()->sendKeys("1");

            // Edit group relevance equation
            $groupRelevance = self::$webDriver->findElement(WebDriverBy::id('grelevance'));
            $groupRelevance->clear()->sendKeys("1");

            // Switch to German tab.
            self::$webDriver->executeScript("window.scrollTo(0, 0);");  // Scroll to top because otherwise the tabs may be hidden under the topbar
            sleep(2);
            // Make sure the language tabs are visible and click on German tab
            $germanTab = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::partialLinkText('German')
                )
            );
            $germanTab->click();

            // Wait a moment for tab content to load
            sleep(1);

            // Ensure the German input field is visible before interacting with it
            self::$webDriver->executeScript("document.getElementById('group_name_de').scrollIntoView(true);");

            // Edit group name in German
            $groupNameGerman = self::$webDriver->wait(10)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('group_name_de')));
            $groupNameGerman->clear()->sendKeys("Edited German name");

            // Edit description in German
            $this->sendTextToEditor("description_de", "Edited German description");

            // Click save.
            $save = self::$webDriver->findElement(WebDriverBy::id('save-button'));
            $save->click();

            // Check the value in the DB
            $oGroup = \QuestionGroup::model()->with('questiongroupl10ns')->findByAttributes(array('gid' => $gid));

            $this->assertNotEmpty($oGroup);
            $this->assertEquals("Edited English name", $oGroup->questiongroupl10ns['en']->group_name);
            $this->assertEquals("Edited German name", $oGroup->questiongroupl10ns['de']->group_name);
            $this->assertEquals("Edited English description", $oGroup->questiongroupl10ns['en']->description);
            $this->assertEquals("Edited German description", $oGroup->questiongroupl10ns['de']->description);
            $this->assertEquals("1", $oGroup->randomization_group);
            $this->assertEquals("1", $oGroup->grelevance);

        } catch (Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    protected function ignorePasswordWarning()
    {
        try {
            $button = self::$webDriver->wait(1)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#admin-notification-modal button.btn-outline-secondary')
                )
            );
            $button->click();
        } catch (TimeOutException $ex) {
            // Do nothing.
        } catch (NoSuchElementException $ex) {
            // Do nothing.
        }
    }

    protected function sendTextToEditor($fieldId, $text)
    {
        // Get default editor mode
        $editorMode = \Yii::app()->getConfig('defaulthtmleditormode');

        switch ($editorMode) {
            case 'inline':
                $iframe = null;
                $driver = self::$webDriver;
                // Wait for question's CKEditor iframe
                self::$webDriver->wait(10)->until(
                    function () use ($driver, &$iframe, $fieldId) {
                        $iframeDiv = $driver->findElement(WebDriverBy::id('cke_' . $fieldId));
                        if (empty($iframeDiv)) return false;
                        $iframe = $iframeDiv->findElement(WebDriverBy::tagName('iframe'));
                        return !empty($iframe);
                    }
                );
                $this->assertNotEmpty($iframe);
                // Switch to question's CKEditor iframe
                self::$webDriver->switchTo()->frame($iframe);

                // Edit the text
                $body = self::$webDriver->findElement(WebDriverBy::tagName('body'));
                $body->click();
                $body->clear();
                $body->click();
                $body->sendKeys($text);
                // Switch back to main content
                self::$webDriver->switchTo()->defaultContent();
                break;
            case 'popup':
            default:
                // Edit the question text
                $question = self::$webDriver->findElement(WebDriverBy::cssSelector('#' . $fieldId));
                $question->clear()->sendKeys($text);
        }
    }
}
