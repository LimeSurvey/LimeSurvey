<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlExportResponsesTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    public static function setupBeforeClass(): void
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
     * Test the export_responses API call.
     */
    public function testExportResponses()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);
        $dbo = \Yii::app()->getDb();

        // Make sure the Authdb is in database (might not be the case if no browser login attempt has been made).
        $plugin = \Plugin::model()->findByAttributes(array('name' => 'Authdb'));
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
        $filename = self::$surveysFolder . '/survey_archive_RemoteControlExportResponses.lsa';
        self::importSurvey($filename);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // Check default export via API.
        $result = $handler->export_responses($sessionKey, self::$surveyId, 'json');
        $this->assertNotNull($result);
        $responses = json_decode(file_get_contents($result->fileName), true);
        $this->assertTrue(count($responses['responses']) === 1);
        $this->assertEquals('Y', $responses['responses'][0]['Q00[SQ001]']);
        // TODO: Currently cannot test the export of "N" responses because of a bug in
        // the LSA export/import process.
        // N are saved as empty, which are exported as null, so can't be reimported as N.
        // Hence, can't prepare the scenario for the N test case
        // $this->assertNull('N', $responses['responses'][0]['Q00[SQ002]']);

        // Check export with Y/N conversion via API.
        $additionalOptions = [
            'convertN' => true,
            'convertY' => true,
            'nValue' => 'A',
            'yValue' => 'B',
        ];
        $result = $handler->export_responses($sessionKey, self::$surveyId, 'json', null, 'all', 'code', 'short', null, null, null, $additionalOptions);
        $this->assertNotNull($result);
        $responses = json_decode(file_get_contents($result->fileName), true);
        $this->assertTrue(count($responses['responses']) === 1);
        $this->assertEquals('B', $responses['responses'][0]['Q00[SQ001]']);
        // $this->assertEquals('A', $responses['responses'][0]['Q00[SQ002]']);

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
