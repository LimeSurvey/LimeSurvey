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

    /**
     * Testing that the responses grid can be filtered by seed.
     */
    public function testSearchFiltersBySeed()
    {
        $matchingId = \SurveyDynamic::model(self::$surveyId)->insertRecords(array('startlanguage' => 'en', 'seed' => '123456789'));
        $otherId = \SurveyDynamic::model(self::$surveyId)->insertRecords(array('startlanguage' => 'en', 'seed' => '987654321'));

        $model = \SurveyDynamic::model(self::$surveyId);
        $model->setAttributes(array('seed' => '123456789'), false);
        $responseIds = array_map(
            function ($response) {
                return (int) $response->id;
            },
            $model->search()->getData()
        );
        $model->seed = null;

        $this->assertContains((int) $matchingId, $responseIds, 'The response with the matching seed should be found.');
        $this->assertNotContains((int) $otherId, $responseIds, 'The response with a different seed should be filtered out.');
    }
}
