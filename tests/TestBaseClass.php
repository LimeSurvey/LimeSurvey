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
        self::$testHelper = new TestHelper();
        self::$dataFolder = self::getDataFolder();
        self::$viewsFolder = self::getViewsFolder();
        self::$surveysFolder = self::getSurveysFolder();
        self::$tempFolder = self::getTempFolder();
        self::$screenshotsFolder = self::getScreenShotsFolder();
        self::$testHelper->importAll();
        parent::setUpBeforeClass();
    }

    // the folder getter can be used in @dataProvider methods since the setUpBeforeClass will run after them

    /**
     * @return string
     */
    public static function getDataFolder(){
        return __DIR__."/resources/data";
    }

    /**
     * @return string
     */
    public static function getViewsFolder(){
        return self::getDataFolder().DIRECTORY_SEPARATOR.'views';
    }

    /**
     * @return string
     */
    public static function getSurveysFolder(){
        return self::getDataFolder().DIRECTORY_SEPARATOR.'surveys';
    }

    /**
     * @return string
     */
    public static function getTempFolder(){
        return __DIR__."/tmp";
    }

    /**
     * @return string
     */
    public static function getScreenShotsFolder(){
        return self::getTempFolder().DIRECTORY_SEPARATOR.'screenshots';
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
            echo 'Fatal error: found no survey file';
            exit(1);
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
            echo 'Fatal error: Could not import survey';
            exit(2);
        }
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        // Make sure we have permission to delete survey.
        \Yii::app()->session['loginID'] = 1;

        if (self::$testSurvey) {
            if (!self::$testSurvey->delete()) {
                echo 'Fatal error: Could not clean up survey ' . self::$testSurvey->sid . '; errors: ' . json_encode(self::$testSurvey->errors);
                exit(3);
            }
            self::$testSurvey = null;
        }
    }
}
