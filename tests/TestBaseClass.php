<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class TestBaseClass extends TestCase
{
    /**
     * @var TestHelper
     */
    protected static $testHelper = null;

    /** @var  string $tempFolder*/
    protected static $tempFolder;

    /** @var  string $screenshotsFolder */
    protected static $screenshotsFolder;

    /** @var  string $surveysFolder */
    protected static $surveysFolder;

    /** @var  string $dataFolder */
    protected static $dataFolder;

    /** @var  string $viewsFolder */
    protected static $viewsFolder;

    /** @var  \Survey */
    protected static $testSurvey;

    /** @var  integer */
    protected static $surveyId;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$testHelper = new TestHelper();

        self::$dataFolder = __DIR__.'/data';
        self::$viewsFolder = self::$dataFolder."/views";
        self::$surveysFolder = self::$dataFolder.'/surveys';
        self::$tempFolder = __DIR__.'/tmp';
        self::$screenshotsFolder = self::$tempFolder.'/screenshots';
        self::$testHelper->importAll();

        self::$testHelper->connectToOriginalDatabase();
    }


    /**
     * @param string $fileName
     * @return void
     */
    protected static function importSurvey($fileName)
    {
        \Yii::app()->session['loginID'] = 1;
        $surveyFile = $fileName;
        if (!file_exists($surveyFile)) {
            die('Fatal error: found no survey file');
        }

        $translateLinksFields = false;
        $newSurveyName = null;
        $result = \importSurveyFile(
            $surveyFile,
            $translateLinksFields,
            $newSurveyName,
            null
        );
        if ($result) {
            self::$testSurvey = \Survey::model()->findByPk($result['newsid']);
            self::$surveyId = $result['newsid'];
        } else {
            die('Fatal error: Could not import survey');
        }
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        if(self::$testSurvey){
            if (!self::$testSurvey->delete()) {
                die(
                    'Fatal error: Could not clean up survey ' . self::$testSurvey->sid . '; errors: ' . json_encode(self::$testSurvey->errors)
                );
            }
            self::$testSurvey = null;
        }
    }
}
