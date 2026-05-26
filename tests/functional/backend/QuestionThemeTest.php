<?php

namespace ls\tests\controllers;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Interactions\WebDriverActions;
use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;
use QuestionTheme;
use QuestionAttribute;
use ExtensionConfig;

/**
 * Uses test data from tpartner: https://github.com/tpartner/LimeSurvey-Range-Slider-4x
 *
 * @group question
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

        // Poll database until the theme appears (up to 20 seconds)
        $theme = null;
        for ($i = 0; $i < 20; $i++) {
            sleep(1);
            $theme = QuestionTheme::model()->findByAttributes(['name' => 'Range-Slider']);
            if (!empty($theme)) {
                break;
            }
        }
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

            $button = $web->findById('questionEditorButton');
            $button->click();

            $button = $web->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('trigger_questionTypeSelector_button')
                )
            );
            // Scroll it into view and click
            $web->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$button]);
            sleep(1);
            $button->click();
            sleep(1);

            // Wait for the Mask questions group to be clickable
            $group = $web->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//button[contains(@class, 'accordion-button') and contains(text(),'Mask questions')]")
                )
            );
            // Scroll it into view and click
            $web->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$group]);
            sleep(1);
            $group->click();
            sleep(1);

            $question = $web->findByPartialLinkText('Range Slider');
            $question->click();

            $button = $web->findById('selector__select-this-questionTypeSelector');
            $button->click();
            sleep(1);

            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(2);

            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('button-collapse-Custom_options')
                )
            );
            $button->click();

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

            // Add values to custom attributes
            $name = 'advancedSettings[custom options][range_slider_min]';
            $input = $web->findByName($name);
            $input->clear()->sendKeys('1');

            $name = 'advancedSettings[custom options][range_slider_max]';
            $input = $web->findByName($name);
            $input->clear()->sendKeys('10');

            self::$webDriver->executeScript('window.scrollTo(0,0);');

            // Save question
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#save-button-create-question')
                )
            );
            $button->click();

            // Wait for save to complete: the page reloads and the save button becomes clickable again.
            self::$webDriver->wait(20)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#save-button-create-question')
                )
            );

            // Force DB reconnect to see changes committed by the web server (MySQL REPEATABLE READ).
            \Yii::app()->db->setActive(false);
            \Yii::app()->db->setActive(true);

            // Poll database until the attribute is saved (up to 10 seconds).
            $rangeSliderMin = null;
            for ($i = 0; $i < 10; $i++) {
                $rangeSliderMin = QuestionAttribute::model()->findByAttributes(
                    [
                        'qid' => self::$testSurvey->questions[0]->qid,
                        'attribute' => 'range_slider_min'
                    ]
                );
                if ($rangeSliderMin !== null) {
                    break;
                }
                sleep(1);
                \Yii::app()->db->setActive(false);
                \Yii::app()->db->setActive(true);
            }
            $this->assertNotNull($rangeSliderMin, 'range_slider_min attribute not found in DB after polling');
            $this->assertEquals('1', $rangeSliderMin->value);

            $rangeSliderMax = QuestionAttribute::model()->findByAttributes(
                [
                    'qid' => self::$testSurvey->questions[0]->qid,
                    'attribute' => 'range_slider_max'
                ]
            );
            $this->assertNotNull($rangeSliderMax, 'range_slider_max attribute not found in DB');
            $this->assertEquals('10', $rangeSliderMax->value);
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
        // Import lsa
        $surveyFile = self::$surveysFolder . '/survey_archive_222923_executeQuestionThemeSurvey.lsa';
        self::importSurvey($surveyFile);
        $urlMan = \Yii::app()->urlManager;
        $web = self::$webDriver;

        // Go to survey overview. Dismiss any beforeunload alert from the previous test.
        $url = $urlMan->createUrl(
            'surveyAdministration/view/surveyid/' . self::$surveyId
        );
        // Dismiss alerts repeatedly — the beforeunload handler may trigger multiple times.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                self::$webDriver->get($url);
                // Verify the page loaded by finding the execute button.
                self::$webDriver->findById('execute_survey_button');
                break;
            } catch (\Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {
                try {
                    self::$webDriver->switchTo()->alert()->accept();
                } catch (\Exception $ignore) {
                }
            }
        }

        // Run survey
        $button = self::$webDriver->findById('execute_survey_button');
        $initialHandles = self::$webDriver->getWindowHandles();
        $button->click();

        // Wait for new tab to open.
        self::$webDriver->wait(15)->until(function () use ($initialHandles) {
            return count(self::$webDriver->getWindowHandles()) > count($initialHandles);
        });

        // Switch to new tab.
        $windowHandles = self::$webDriver->getWindowHandles();
        self::$webDriver->switchTo()->window(
            end($windowHandles)
        );

        // Wait for the slider to be present on the survey page.
        $web->wait(15)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('.slider-handle')
            )
        );

        // Drag each slider handle 50px left and back to trigger value recording.
        $handles = $web->findElements(WebDriverBy::cssSelector('.slider-handle'));
        $this->assertNotEmpty($handles, 'Expected slider handles to be present on the page');
        $action = new WebDriverActions($web);
        foreach ($handles as $handle) {
            $action->clickAndHold($handle)->moveByOffset(-50, 0)->moveByOffset(50, 0)->release()->perform();
        }

        // Submit
        $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $nextButton->click();
        sleep(1);

        // Check result in database
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);

        $qid = self::$testSurvey->questions[0]->qid;
        $this->assertGreaterThanOrEqual(2, count(self::$testSurvey->questions[0]->subquestions), 'Expected at least 2 subquestions');
        $sqid1 = self::$testSurvey->questions[0]->subquestions[0]->qid;
        $sqid2 = self::$testSurvey->questions[0]->subquestions[1]->qid;

        $qCode1 = sprintf('Q%d_S%d', $qid, $sqid1);
        $qCode2 = sprintf('Q%d_S%d', $qid, $sqid2);

        $this->assertEquals(4, (int) $responses[0]->attributes[$qCode1]);
        $this->assertEquals(7, (int) $responses[0]->attributes[$qCode2]);
    }
}
