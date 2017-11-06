<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class TestBaseClass extends TestCase
{
    /**
     * @var TestHelper
     */
    protected static $testHelper = null;


    /**
     * @var int
     */
    public static $surveyId = null;

    public function setUp()
    {
        parent::setUp();
        self::$testHelper = new TestHelper();

        self::$testHelper->importAll();

    }


    protected static function importSurvey($fileName){
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
            self::$surveyId = $result['newsid'];
        } else {
            die('Fatal error: Could not import survey');
        }
    }
}
