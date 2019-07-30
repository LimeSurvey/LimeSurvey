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
        $dbo = \Yii::app()->getDb();

        // Make sure the Authdb is in database (might not be the case if no browser login attempt has been made).
        $plugin = \Plugin::model()->findByAttributes(array('name'=>'Authdb'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'Authdb';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
        // Clear login attempts.
        $query = sprintf('DELETE FROM {{failed_login_attempts}}');
        $dbo->createCommand($query)->execute();

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
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Get sgqa.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $question = $survey->groups[0]->questions[0];
        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $question->qid;

        // Add response
        $response = [
            $sgqa => 'One answer'
        ];
        $result = $handler->add_response($sessionKey, self::$surveyId, $response);
        $this->assertEquals('19', $result, '$result = ' . json_encode($result));

        // Check result via database.
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
