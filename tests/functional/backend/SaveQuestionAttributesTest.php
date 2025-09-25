<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\LocalFileDetector;

/**
 * @since 2021-11-09
 * @group questionattribute
 */
class SaveQuestionAttributesTest extends TestBaseClassWeb
{
    public static $newUserId = null;

    /**
     * Setup
     */
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        $oUser = self::createUserWithPermissions(
            [
                "users_name" => "surveyadmin",
                "full_name" => "surveyadmin",
                "email" => "surveyadmin@example.com",
                "lang" => "en",
                "password" => "surveyadmin"
            ],
            [
                'settings' => [
                    'read' => 'on',
                    'update' => 'on',
                    'import' => 'on'
                ],
                'surveys' => [
                    'create' => 'on',
                    'read' => 'on',
                    'update' => 'on',
                    'delete' => 'on',
                    'export' => 'on'
                ],
                'auth_db' => [
                    'read' => 'on'
                ],
            ]
        );

        self::$newUserId = $oUser->uid;
    }

    public function testSaveQuestionAttributes()
    {
        // Import survey.
        $surveyFile =  'tests/data/surveys/limesurvey_survey_141451_testSaveQuestionAttributes.lss';
        self::importSurvey($surveyFile);

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertNotEmpty($survey);
        $this->assertCount(1, $survey->groups, 'Wrong number of groups: ' . count($survey->groups));
        $this->assertCount(1, $survey->groups[0]->questions, 'We have exactly one question');

        try {
            // Browser login.
            self::adminLogin('surveyadmin', 'surveyadmin');

            $this->importDemoPlugin();

            $urlMan = \Yii::app()->urlManager;
            $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
            $url = $urlMan->createUrl(
                'questionAdministration/view',
                [
                    //'sa'       => 'view',
                    'surveyid' => self::$surveyId,
                    'gid'      => $survey->groups[0]->gid,
                    'qid'      => $survey->groups[0]->questions[0]->qid
                ]
            );

            $web = self::$webDriver;
            $web->get($url);

            sleep(2);

            $web->dismissModal();
            $web->dismissModal();

            sleep(5);
            $oElementQuestionEditorButton = $this->waitForElementShim($web, '#questionEditorButton');
            $web->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#questionEditorButton')));
            $oElementQuestionEditorButton->click();
            sleep(1);

            $displayGeneralSettingsTitle = $web->findElement(WebDriverBy::id('button-collapse-General'));
            $displayGeneralSettingsTitle->click();

            $oElementAdvancedOptionsPanel = $this->waitForElementShim($web, '#advanced-options-container');
            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($oElementAdvancedOptionsPanel));

            $displayCategoryTitle = $web->findElement(WebDriverBy::id('button-collapse-Display'));
            $displayCategoryTitle->click();

            sleep(1);

            $attribute1 = $web->findElement(WebDriverBy::id('advancedSettings_display_cssclass'));
            $attribute1->sendKeys('test-class<script>console.log("Test");</script>');

            $web->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            $testCategoryTitle = $web->findElement(WebDriverBy::id('button-collapse-Test'));
            $testCategoryTitle->click();

            sleep(1);

            $attribute2 = $web->findElement(WebDriverBy::id('advancedSettings_test_nonFilteredAttribute'));
            $attribute2->sendKeys('<script>console.log(1);</script>');

            $savebutton = $web->findElement(WebDriverBy::id('save-and-close-button-create-question'));
            $savebutton->click();

            $alert = $this->waitForElementShim($web, '#notif-container .alert', 20);
            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($alert));

            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__ . '_afterSave');

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }

        $qid = $survey->groups[0]->questions[0]->qid;

        // Test a filtered attribute
        $filteredAttribute = \QuestionAttribute::model()->findByAttributes(['qid' => $qid, 'attribute' => 'cssclass']);
        $this->assertEquals("test-class", $filteredAttribute->value);

        // Test a non-filtered attribute
        $nonFilteredAttribute = \QuestionAttribute::model()->findByAttributes(['qid' => $qid, 'attribute' => 'nonFilteredAttribute']);
        $this->assertEquals("<script>console.log(1);</script>", $nonFilteredAttribute->value);
    }

    public static function tearDownAfterClass(): void
    {
        $oUser = \User::model()->findByPk(self::$newUserId);
        $oUser->delete();
        parent::tearDownAfterClass();
    }

    protected function importDemoPlugin()
    {
        // Delete demo plugin if it exists
        $uploadedPluginsDir = \Yii::getPathOfAlias('uploaddir.plugins');
        $pluginDir = $uploadedPluginsDir . '/NewQuestionAttributesPlugin';
        if (file_exists($pluginDir)) {
            \Yii::import('application.helpers.common_helper');
            rmdirr($pluginDir);
        }

        $plugin = \Plugin::model()->findByAttributes(['name' => 'NewQuestionAttributesPlugin']);
        if (!empty($plugin)) {
            $plugin->delete();
        }

        $urlMan = \Yii::app()->urlManager;
        $web = self::$webDriver;
        
        try {
            // Go to plugin manager page
            $url = $urlMan->createUrl('admin/pluginmanager/sa/index');
            $web->get($url);

            $button = $this->waitForElementShim($web, '[data-bs-target="#installPluginZipModal"]');
            $web->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('[data-bs-target="#installPluginZipModal"]')));
            $button->click();

            // Upload the file
            $web->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#installPluginZipModal #the_file')));
            $fileInput = $web->findByCss('#installPluginZipModal #the_file');
            $fileInput->setFileDetector(new LocalFileDetector());
            $file = ROOT . '/tests/data/file_upload/NewQuestionAttributesPlugin.zip';
            $this->assertTrue(file_exists($file));
            $fileInput->sendKeys($file)->submit();

            $button = $this->waitForElementShim($web, '[type="submit"][value="Install"]');
            $web->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('[type="submit"][value="Install"]')));
            $button = $web->findByCss('[type="submit"][value="Install"]');
            $button->click();

            sleep(2);
        } catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
            // Dump for debugging.
            $web->dumpBody();

            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }

        // Check result in database
        $plugin = \Plugin::model()->findByAttributes(['name' => 'NewQuestionAttributesPlugin']);
        $this->assertNotEmpty($plugin);

        // Check result in filesystem
        $this->assertTrue(file_exists($pluginDir));

        // Activate the plugin
        $plugin->active = 1;
        $plugin->save();
    }
}
