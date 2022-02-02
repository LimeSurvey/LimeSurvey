<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClassWeb;
/*use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;
use QuestionTheme;
use QuestionAttribute;
use ExtensionConfig;*/

/**
 * Uses test data from tpartner: https://github.com/tpartner/LimeSurvey-Range-Slider-4x
 */
class CopySurveyResourcesTest extends TestBaseClassWeb
{
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        /*$username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }*/

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        //self::adminLogin($username, $password);
    }

    /**
     * Copy survey resources
     */
    public function testCopySurveyResources()
    {
        // Import survey with one group and question
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_994476_CopySurveyResources.lss';
        self::importSurvey($surveyFile);
        $sourceSid = self::$testSurvey->sid;

        // Add resource
        $basedestdir = \Yii::app()->getConfig('uploaddir') . "/surveys";
        $destdir = $basedestdir . "/$sourceSid/images/";
        if (!is_dir($destdir)) {
            mkdir($destdir, 777, true);
        }
        $file = BASEPATH . '../tests/data/file_upload/dalahorse.jpg';
        copy($file, $destdir . "dalahorse.jpg");
        $this->assertTrue(file_exists($destdir . "dalahorse.jpg"));

        // Copy survey
        \Yii::app()->loadHelper('export');
        \Yii::import('application.helpers.admin.import_helper', true);
        $sourceData = \surveyGetXMLData($sourceSid);
        $importResults = \XMLImportSurvey('', $sourceData);
        $this->assertIsArray($importResults);
        $this->assertArrayHasKey('newsid', $importResults);

        $targetSid = $importResults['newsid'];

        // Copy resources
        $resourceCopier = new \LimeSurvey\Models\Services\CopySurveyResources();
        [$copiedFilesInfo, $errorFilesInfo] = $resourceCopier->copyResources($sourceSid, $targetSid);
        $this->assertEmpty($errorFilesInfo);
        $this->assertNotEmpty($copiedFilesInfo);
        $this->assertEquals("dalahorse.jpg", $copiedFilesInfo[0]['filename']);
    }
}
