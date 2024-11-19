<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API Quota methods.
 */
class RemoteControlQuotaTest extends TestBaseClass
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
     * Test the quota RPC methods.
     */
    public function testQuota()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
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


        $filename = self::$surveysFolder . '/limesurvey_survey_remote_api_group_language.lss';
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

        // Add quota
        $quota_id = $handler->add_quota(
            $sessionKey,
            self::$surveyId,
            'Test quota name',
            150,
            true,
            'confirm_terminate',
            true,
            'Quota reached',
            'https://example.com',
            'This is the Quota URL'
        );
        $this->assertIsNumeric($quota_id, '$quota_id = ' . json_encode($quota_id));

        $oQuota = \Quota::model()->findByPk($quota_id);
        $this->assertNotEmpty($oQuota, 'Added quota not found');

        $this->assertEquals(self::$surveyId, $oQuota->sid);
        $this->assertEquals('Test quota name', $oQuota->name);
        $this->assertEquals(150, $oQuota->qlimit);
        $this->assertEquals(1, $oQuota->active);
        $this->assertEquals(2, $oQuota->action);
        $this->assertEquals(1, $oQuota->autoload_url);
        $this->assertEquals('Quota reached', $oQuota->mainLanguagesetting->quotals_message);
        $this->assertEquals('https://example.com', $oQuota->mainLanguagesetting->quotals_url);
        $this->assertEquals('This is the Quota URL', $oQuota->mainLanguagesetting->quotals_urldescrip);

        // List quotas
        $quotas = $handler->list_quotas($sessionKey, self::$surveyId);
        $this->assertIsArray($quotas);
        $this->assertEquals(1, count($quotas));
        $this->assertEquals($quota_id, $quotas[0]['id']);

        // Update quota properties
        $set_result = $handler->set_quota_properties(
            $sessionKey,
            $quota_id,
            array('qlimit' => 200)
        );
        $this->assertTrue($set_result['success']);
        $this->assertEquals(200, $set_result['message']['qlimit']);

        // Get quota properties
        $properties = $handler->get_quota_properties($sessionKey, $quota_id);
        $this->assertEquals(200, $properties['qlimit']);

        // Delete quota
        $delete_result = $handler->delete_quota($sessionKey, $quota_id);
        $this->assertEquals('OK', $delete_result['status']);

        // No quotas
        $no_quotas_result = $handler->list_quotas($sessionKey, self::$surveyId);
        $this->assertEquals('No quotas found', $no_quotas_result['status']);

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
