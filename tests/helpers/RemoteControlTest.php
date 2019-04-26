<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 *
 * @since 2019-04-26
 */
class RemoteControlTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$username = getenv('ADMINUSERNAME');
        if (!self::$username) {
            self::$username = 'admin';
        }

        self::$password = getenv('PASSWORD');
        if (!self::$password) {
            self::$password = 'password';
        }
    }

    /**
     * Test the add_response API call.
     */
    public function testAddResponse()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

        // Import survey
        $filename = self::$surveysFolder . '/limesurvey_survey_666368.lss';
        self::importSurvey($filename);
        self::$testHelper->activateSurvey(self::$surveyId);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );

        // Get sgqa.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $question = $survey->groups[0]->questions[0];
        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $question->qid;

        // Add response
        $response = [
            $sgqa => 'One answer'
        ];
        $result = $handler->add_response($sessionKey, self::$surveyId, $response);

        // Check result via database.
        $dbo = \Yii::app()->getDb();
        $query = sprintf('SELECT * FROM {{survey_%d}}', self::$surveyId);
        $result = $dbo->createCommand($query)->queryAll();
        $this->assertCount(1, $result, 'Exactly one response');
        $this->assertEquals('One answer', $result[0][$sgqa], '"One answer" response');

        // Check result via API.
        $result = $handler->export_responses($sessionKey, self::$surveyId, 'json');
        $this->assertNotNull($result);
        $responses = json_decode(file_get_contents($result->fileName));
        $this->assertTrue(count($responses->responses) === 1);

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
