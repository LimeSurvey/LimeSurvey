<?php

namespace ls\tests;

use Yii;

class SurveyDynamicTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();

        // Import survey.
        $filename = self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss';
        self::importSurvey($filename);

        // Activate survey.
        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * Testing that a new response can be correctly inserted.
     */
    public function testInsertResponse()
    {
        $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords(array('startlanguage' => 'en'));
        $response = \SurveyDynamic::model()->findByPk($responseId);

        $this->assertIsNumeric($responseId, 'The newly inserted response id should have been returned.');
        $this->assertInstanceOf('SurveyDynamic', $response, 'The newly inserted response should have been returned.');
    }

    /**
     * Testing that an exception is thrown when
     * response insertion fails.
     */
    public function testErrorInsertingResponse()
    {
        $this->expectException(\CException::class);

        // Table column name incorrectly spelled.
        $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords(array('starlanguage' => 'en'));
    }
}
