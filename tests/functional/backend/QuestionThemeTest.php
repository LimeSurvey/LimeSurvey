<?php

namespace ls\tests\controllers;

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

        $button = $web->findByLinkText('Upload & install');
        $button->click();

        sleep(1);

        // Click "Import"
        $fileInput = $web->findByCss('#importQuestionTemplate #the_file');
        $fileInput->setFileDetector(new LocalFileDetector());
        $file = BASEPATH . '../tests/data/file_upload/rangeslider_tpartner.zip';
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

        // Select question theme
        $button = $web->findById('trigger_questionTypeSelector_button');
        $button->click();

        $group = $web->findByLinkText('Mask questions');
        $group->click();

        $question = $web->findByPartialLinkText('Range Slider');
        $question->click();

        $button = $web->findById('selector__select-this-questionTypeSelector');
        $button->click();
        sleep(1);

        $button = $web->findById('button-collapse-Custom_options');
        $button->click();

        // Check that all custom attributes are displayed
        $themeDir = \Yii::app()->getConfig('userquestionthemerootdir') . '/Range-Slider';
        $file = BASEPATH . '../tests/data/file_upload/rangeslider_tpartner.zip';
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

        // Save question
        $button = $web->findById('save-button-create-question');
        $button->click();
        sleep(1);

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
    }

    /**
     * @depends testSelectQuestionThemeInQuestionEditor
     */
    public function testPreviewQuestionThemeQuestion()
    {
        // Use previous survey
        // Activate survey
        // Execute survey
        // Answer question using question theme
        // Submit
        // Check result in database
    }
}
