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
                "lang" => "auto",
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
        $surveyFile =  'tests/data/surveys/limesurvey_survey_458461_testSaveQuestionAttributes.lss';
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
                'admin/questions',
                [
                    'sa'       => 'editquestion',
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

            $oElementAdvancedOptionsPanel = $this->waitForElementShim($web, '#container-advanced-question-settings');
            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOf($oElementAdvancedOptionsPanel));

            $displayCategoryTitle = $oElementAdvancedOptionsPanel->findElement(WebDriverBy::linkText("Display"));
            $displayCategoryTitle->click();

            sleep(1);

            $attribute1 = $web->findElement(WebDriverBy::id('cssclass'));
            $attribute1->sendKeys('test-class<script>console.log("Test");</script>');

            $testCategoryTitle = $oElementAdvancedOptionsPanel->findElement(WebDriverBy::linkText('Test'));
            $testCategoryTitle->click();

            sleep(1);

            $attribute2 = $web->findElement(WebDriverBy::id('nonFilteredAttribute'));
            $attribute2->sendKeys('<script>console.log(1);</script>');

            $savebutton = $web->findElement(WebDriverBy::id('save-and-close-button'));
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
    }

    protected function importDemoPlugin()
    {
        // Delete demo plugin if it exists
        $userPluginsDir = BASEPATH . '../plugins';
        $pluginDir = $userPluginsDir . '/NewQuestionAttributesPlugin';
        if (file_exists($pluginDir)) {
            \Yii::import('application.helpers.common_helper');
            rmdirr($pluginDir);
        }

        \Yii::app()->loadLibrary('admin.pclzip');
        $zip = new \PclZip(BASEPATH . '../tests/data/file_upload/NewQuestionAttributesPlugin.zip');
        $extractResult = $zip->extract(PCLZIP_OPT_PATH, $userPluginsDir);

        $this->assertNotEmpty($extractResult);
        
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'NewQuestionAttributesPlugin'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'NewQuestionAttributesPlugin';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }
    }
}
