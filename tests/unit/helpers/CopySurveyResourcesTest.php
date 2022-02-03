<?php

namespace ls\tests;

/**
 * Tests the CopySurveyResources service class
 * @since 2022-02-02
 * @group copysurveyresources
 */
class CopySurveyResourcesTest extends TestBaseClass
{
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
        exec('sudo chmod -R 777 ' . \Yii::app()->getConfig('uploaddir')); // Add permisions to ./upload directory, neede for CI pipeline
        $basedestdir = \Yii::app()->getConfig('uploaddir') . "/surveys";
        $destdir = $basedestdir . "/$sourceSid/images/";
        if (!is_dir($destdir)) {
            $dirCreated = mkdir($destdir, 0777, true);
        }
        $this->assertTrue($dirCreated, "Couldn't create dir '$destdir'");
        $file = self::$dataFolder .'/file_upload/dalahorse.jpg';
        $this->assertTrue(file_exists($file));
        copy($file, $destdir . "dalahorse.jpg");
        $this->assertTrue(file_exists($destdir . "dalahorse.jpg"));

        // Copy survey
        \Yii::import('application.helpers.export_helper', true);
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
