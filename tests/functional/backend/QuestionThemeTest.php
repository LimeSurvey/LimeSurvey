<?php

namespace ls\tests\controllers;

use Facebook\WebDriver\WebDriverExpectedCondition;
use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;
use QuestionTheme;
use QuestionAttribute;
use ExtensionConfig;

/**
 * Uses test data from tpartner: https://github.com/tpartner/LimeSurvey-Range-Slider-4x
 */
class QuestionThemeTest extends TestBaseClassWeb
{
    public static function setUpBeforeClass(): void
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

        self::adminLogin($username, $password);
    }

    public function testImportQuestionTheme()
    {

        // Delete question theme if it exists
        $theme = QuestionTheme::model()->findByAttributes(['name' => 'Range-Slider']);
        $themeDir = \Yii::app()->getConfig('userquestionthemerootdir') . '/Range-Slider';
        if (!empty($theme)) {
            \Yii::import('application.helpers.common_helper', true);
            rmdirr($themeDir);
            $theme->delete();
        }

        $urlMan = \Yii::app()->urlManager;
        $web = self::$webDriver;

        // Go to theme page
        $url = $urlMan->createUrl('themeOptions/index');
        $web->get($url);
        sleep(1);

        $button = $web->findByLinkText('Question themes');
        $button->click();

        $button = $web->findById('uploadandinstall');
        $button->click();

        sleep(1);

        // Click "Import"
        $fileInput = $web->findByCss('#importQuestionTemplate #the_file');
        $fileInput->setFileDetector(new LocalFileDetector());
        $file = ROOT . '/tests/data/file_upload/rangeslider_tpartner.zip';
        $this->assertTrue(file_exists($file));
        $fileInput->sendKeys($file)->submit();

        sleep(1);

        // Check result in database
        $theme = QuestionTheme::model()->findByAttributes(['name' => 'Range-Slider']);
        $this->assertNotEmpty($theme, 'Found installed question theme in database');

        // Check result in filesystem
        $this->assertTrue(file_exists($themeDir), 'Question theme exists in userquestionthemerootdir after install');
    }

    /**
     * Assumes question theme has been imported from previous test.
     *
     * @depends testImportQuestionTheme
     */
    public function testSelectQuestionThemeInQuestionEditor()
    {
        try {
            // Import survey with one group and question
            $surveyFile = self::$surveysFolder . '/limesurvey_survey_193959_testSelectQuestionThemeInEditor.lss';
            self::importSurvey($surveyFile);

            $urlMan = \Yii::app()->urlManager;
            $web = self::$webDriver;

            // Go to question editor
            $url = $urlMan->createUrl(
                'questionAdministration/view',
                [
                    'surveyid' => self::$testSurvey->sid,
                    'qid' => self::$testSurvey->questions[0]->qid
                ]
            );
            $web->get($url);

            // Wait for and click the question editor button
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('questionEditorButton')
                )
            );
            $button->click();
            sleep(1);

            // Select question theme - ensure button is clickable
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('trigger_questionTypeSelector_button')
                )
            );
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $button);
            sleep(1);
            $button->click();

            // Scroll to top to make sure the question type groups are visible
            self::$webDriver->executeScript("window.scrollTo(0, 0);");
            sleep(1);

            // Find the Mask questions group and ensure it's visible before clicking
            $group = $web->findElement(WebDriverBy::xpath("//button[contains(@class, 'accordion-button') and contains(text(),'Mask questions')]"));
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $group);
            sleep(1);
            $group->click();
            sleep(1);

            // Find and click the Range Slider option
            $question = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::partialLinkText('Range Slider')
                )
            );
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $question);
            sleep(1);
            $question->click();

            // Click the select button
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('selector__select-this-questionTypeSelector')
                )
            );
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $button);
            sleep(1);
            $button->click();
            sleep(1);

            // Scroll to the bottom to find the Custom options section
            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(2);

            // Click on the Custom options collapse button
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('button-collapse-Custom_options')
                )
            );
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $button);
            sleep(1);
            $button->click();
            sleep(1);

            // Check that all custom attributes are displayed
            $themeDir = \Yii::app()->getConfig('userquestionthemerootdir') . '/Range-Slider';
            $file = ROOT . '/tests/data/file_upload/rangeslider_tpartner.zip';
            /** @var ExtensionConfig */
            $config = ExtensionConfig::loadFromZip($file);
            $this->assertNotEmpty($config, 'Loading config.xml from range slider zip file');
            /** @var SimpleXMLElement */
            $attributes = $config->xml->attributes;
            $found = 0;
            foreach ($attributes->attribute as $attribute) {
                if ((string) $attribute->category === 'Custom options') {
                    $name = sprintf(
                        'advancedSettings[custom options][%s]',
                        (string) $attribute->name
                    );
                    if ((int) $attribute->i18n) {
                        $name .= '[en]';
                    }
                    $input = $web->findByName($name);
                    if (!empty($input)) {
                        $found++;
                    }
                }
            }
            $this->assertEquals(16, $found, 'Found exactly 10 customer options');

            // Add values to custom attributes - ensure inputs are visible
            $name = 'advancedSettings[custom options][range_slider_min]';
            $input = $web->findByName($name);
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $input);
            $input->clear()->sendKeys('1');

            $name = 'advancedSettings[custom options][range_slider_max]';
            $input = $web->findByName($name);
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $input);
            $input->clear()->sendKeys('10');

            // Scroll to top for the save button
            self::$webDriver->executeScript('window.scrollTo(0,0);');
            sleep(1);

            // Save question
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#save-button-create-question')
                )
            );
            self::$webDriver->executeScript("arguments[0].scrollIntoView(true);", $button);
            sleep(1);
            $button->click();
            sleep(2);

            // Check database
            $rangeSliderMin = QuestionAttribute::model()->findByAttributes(
                [
                    'qid' => self::$testSurvey->questions[0]->qid,
                    'attribute' => 'range_slider_min'
                ]
            );
            $this->assertEquals('1', $rangeSliderMin->value);

            $rangeSliderMin = QuestionAttribute::model()->findByAttributes(
                [
                    'qid' => self::$testSurvey->questions[0]->qid,
                    'attribute' => 'range_slider_max'
                ]
            );
            $this->assertEquals('10', $rangeSliderMin->value);
        } catch (\Exception $e) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($e)
            );
        }
    }

    /**
     * @depends testImportQuestionTheme
     */
    public function testExecuteQuestionThemeSurvey()
    {
        $this->markTestSkipped('external theme needs to be updated');
        // Import lsa
        $surveyFile = self::$surveysFolder . '/survey_archive_222923_executeQuestionThemeSurvey.lsa';
        self::importSurvey($surveyFile);

        $urlMan = \Yii::app()->urlManager;
        $web = self::$webDriver;

        // Go to survey overview.
        $url = $urlMan->createUrl(
            'surveyAdministration/view/surveyid/' . self::$surveyId
        );
        self::$webDriver->get($url);

        // Run survey
        $button = self::$webDriver->findById('execute_survey_button') ;
        $button->click();
        sleep(1);

        // Switch to new tab.
        $windowHandles = self::$webDriver->getWindowHandles();
        self::$webDriver->switchTo()->window(
            end($windowHandles)
        );
        sleep(1);

        // Click on slider to trigger answering.
        $sliderHandler = $web->findByCss('.slider-handle');
        $sliderHandler->click();

        // Submit
        $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $nextButton->click();
        sleep(1);

        // Check result in database
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);

        $sid = self::$surveyId;
        $gid = self::$testSurvey->groups[0]->gid;
        $qid = self::$testSurvey->questions[0]->qid;

        $sgqa1 = sprintf('%dX%dX%dSQ001', $sid, $gid, $qid);
        $sgqa2 = sprintf('%dX%dX%dSQ002', $sid, $gid, $qid);

        $this->assertEquals(4, (int) $responses[0]->attributes[$sgqa1]);
        $this->assertEquals(7, (int) $responses[0]->attributes[$sgqa2]);
    }
}
