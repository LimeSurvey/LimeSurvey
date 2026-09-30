<?php

namespace ls\tests;

/**
 * Tests for the AuditLog core plugin.
 */
class AuditLogTest extends TestBaseClass
{
    /** @var \AuditLog */
    private static $plugin;

    /** @var bool Whether AuditLog was already active before this test class ran */
    private static $wasActive;

    /** @var mixed The web user id before this test class ran */
    private static $previousUserId;

    /**
     * Activate and load AuditLog, create its log table and act as the superadmin.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $pluginRecord = \Plugin::model()->findByAttributes(['name' => 'AuditLog']);
        self::$wasActive = $pluginRecord && $pluginRecord->active == 1;
        $pluginRecord = self::installAndActivatePlugin('AuditLog');
        self::$plugin = App()->getPluginManager()->loadPlugin('AuditLog', $pluginRecord->id);
        // Creates the log table if it does not exist yet
        self::dispatchPluginEvent('AuditLog', 'beforeActivate', []);

        \Yii::app()->session['loginID'] = 1;
        self::$previousUserId = \Yii::app()->user->getId();
        \Yii::app()->user->setId(1);
    }

    /**
     * Leave AuditLog and the web user as they were found, so they do not affect other test classes.
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->user->setId(self::$previousUserId);
        if (!self::$wasActive) {
            // Every handler is subscribed under its own method name
            foreach (get_class_methods(self::$plugin) as $method) {
                App()->getPluginManager()->unsubscribe(self::$plugin, $method);
            }
            self::deActivatePlugin('AuditLog');
        }
        parent::tearDownAfterClass();
    }

    /**
     * Restore the default of the global setting a test may have switched off.
     */
    protected function tearDown(): void
    {
        self::$plugin->saveSettings(['AuditLog_Log_SurveyDelete' => '1']);
        parent::tearDown();
    }

    /**
     * A deleted survey leaves one entry saying who deleted which survey,
     * with its titles in all languages so it can still be identified.
     */
    public function testSurveyDeletionIsLoggedWithSurveyIdAndTitles()
    {
        $surveyId = $this->importTestSurvey();
        $this->setSurveyTitles($surveyId, ['en' => 'Audit log test (en)', 'es' => 'Audit log test (es)']);
        $lastLogId = $this->getLastLogId();

        $this->deleteSurvey($surveyId);

        $rows = $this->getLogRowsAfter($lastLogId);
        $this->assertCount(1, $rows);
        $this->assertSame('survey', $rows[0]['entity']);
        $this->assertSame((string) $surveyId, $rows[0]['entityid']);
        $this->assertSame('delete', $rows[0]['action']);
        $this->assertSame('1', $rows[0]['uid']);
        $oldValues = json_decode($rows[0]['oldvalues'], true);
        $this->assertEquals($surveyId, $oldValues['sid']);
        $this->assertEquals(['en' => 'Audit log test (en)', 'es' => 'Audit log test (es)'], $oldValues['titles']);
    }

    /**
     * The bounce account password is a credential: it must not be copied into the audit log.
     */
    public function testSurveyDeletionLogDoesNotContainBounceAccountPassword()
    {
        $surveyId = $this->importTestSurvey();
        // Stored encrypted, as the bounce settings page does
        $encryptedPassword = \LSActiveRecord::encryptSingle('bounce-secret-123');
        \Survey::model()->updateByPk($surveyId, ['bounceaccountpass' => $encryptedPassword]);
        \Survey::model()->resetCache();
        $lastLogId = $this->getLastLogId();

        $this->deleteSurvey($surveyId);

        $rows = $this->getLogRowsAfter($lastLogId);
        $this->assertCount(1, $rows);
        $this->assertArrayNotHasKey('bounceaccountpass', json_decode($rows[0]['oldvalues'], true));
        $this->assertStringNotContainsString($encryptedPassword, $rows[0]['oldvalues']);
    }

    /**
     * The global "Survey deleted" setting switches the entry off.
     */
    public function testSurveyDeletionIsNotLoggedWhenGlobalSettingIsOff()
    {
        self::$plugin->saveSettings(['AuditLog_Log_SurveyDelete' => '0']);
        $surveyId = $this->importTestSurvey();
        $lastLogId = $this->getLastLogId();

        $this->deleteSurvey($surveyId);

        $this->assertSame([], $this->getLogRowsAfter($lastLogId));
    }

    /**
     * The per-survey "Audit log for this survey" setting must not allow deleting a survey without a trace.
     */
    public function testSurveyDeletionIsLoggedEvenWhenSurveyAuditingIsOff()
    {
        $surveyId = $this->importTestSurvey();
        // Same event the survey settings page dispatches when "Audit log for this survey" is set to "No"
        self::dispatchPluginEvent('AuditLog', 'newSurveySettings', [
            'survey' => $surveyId,
            'settings' => ['auditing' => 0],
        ]);
        $lastLogId = $this->getLastLogId();

        $this->deleteSurvey($surveyId);

        $rows = $this->getLogRowsAfter($lastLogId);
        $this->assertCount(1, $rows);
        $this->assertSame((string) $surveyId, $rows[0]['entityid']);
    }

    /**
     * A failing audit entry must not block the deletion (e.g. bulk delete or RemoteControl):
     * the survey is deleted and the error is written to the plugin log.
     */
    public function testSurveyIsDeletedAndErrorLoggedWhenAuditEntryCannotBeWritten()
    {
        $surveyId = $this->importTestSurvey();
        $db = \Yii::app()->db;
        // Load the log table schema first, so the write fails on insert like on a database failure.
        // Loading it while the table is missing would keep a broken model cached for the rest of the run.
        \PluginDynamic::model($db->tablePrefix . 'auditlog_log');
        // Empty the in-memory log, so only messages logged during this deletion are read below
        \Yii::getLogger()->flush();
        $db->createCommand()->renameTable('{{auditlog_log}}', '{{auditlog_log_unavailable}}');
        try {
            $this->deleteSurvey($surveyId);
        } finally {
            $db->createCommand()->renameTable('{{auditlog_log_unavailable}}', '{{auditlog_log}}');
        }

        $errors = \Yii::getLogger()->getLogs(\CLogger::LEVEL_ERROR, 'plugin.auditlog');
        $this->assertCount(1, $errors);
        $this->assertStringContainsString((string) $surveyId, $errors[0][0]);
    }

    /**
     * Invalid UTF-8 (e.g. from legacy data) must not turn the entry into an empty one:
     * the invalid bytes are replaced and the rest of the survey data is kept.
     */
    public function testSurveyDeletionLogReplacesInvalidUtf8()
    {
        $surveyId = $this->importTestSurvey();
        $survey = \Survey::model()->findByPk($surveyId);
        // "\xB1" on its own is not valid UTF-8
        $survey->admin = "Survey admin \xB1";
        $lastLogId = $this->getLastLogId();

        $this->assertTrue($survey->delete(), 'Survey could not be deleted');
        self::$testSurvey = null;

        $rows = $this->getLogRowsAfter($lastLogId);
        $this->assertCount(1, $rows);
        $oldValues = json_decode($rows[0]['oldvalues'], true);
        $this->assertIsArray($oldValues, 'The entry has no survey data');
        $this->assertEquals($surveyId, $oldValues['sid']);
        $this->assertSame("Survey admin \u{FFFD}", $oldValues['admin']);
    }

    /**
     * A changed bounce account password must not be copied into the "Settings changed" entry either.
     */
    public function testSurveySettingsChangeLogDoesNotContainBounceAccountPassword()
    {
        $surveyId = $this->importTestSurvey();
        $survey = \Survey::model()->findByPk($surveyId);
        $encryptedPassword = \LSActiveRecord::encryptSingle('new-bounce-secret-456');
        $survey->bounceaccountpass = $encryptedPassword;
        $survey->admin = 'New survey admin';
        $lastLogId = $this->getLastLogId();

        // Same event the survey general settings service dispatches before saving the survey
        self::dispatchPluginEvent('AuditLog', 'beforeSurveySettingsSave', ['modifiedSurvey' => $survey]);

        $rows = $this->getLogRowsAfter($lastLogId);
        $this->assertCount(1, $rows);
        $this->assertSame('update', $rows[0]['action']);
        $this->assertSame('admin', $rows[0]['fields']);
        $this->assertStringNotContainsString('bounceaccountpass', $rows[0]['oldvalues'] . $rows[0]['newvalues']);
        $this->assertStringNotContainsString($encryptedPassword, $rows[0]['newvalues']);

        // The changes above were never saved
        \Survey::model()->resetCache();
        $this->deleteSurvey($surveyId);
    }

    /**
     * Import a survey with English as base language and Spanish as additional language.
     * @return int
     */
    private function importTestSurvey(): int
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_854771.lss');
        return (int) self::$surveyId;
    }

    /**
     * @param int $surveyId
     * @param string[] $titles Titles indexed by language code
     */
    private function setSurveyTitles(int $surveyId, array $titles): void
    {
        foreach ($titles as $language => $title) {
            \SurveyLanguageSetting::model()->updateAll(
                ['surveyls_title' => $title],
                'surveyls_survey_id = :sid AND surveyls_language = :language',
                [':sid' => $surveyId, ':language' => $language]
            );
        }
        \Survey::model()->resetCache();
    }

    /**
     * Delete the survey the way the admin interface and RemoteControl do.
     * @param int $surveyId
     */
    private function deleteSurvey(int $surveyId): void
    {
        $this->assertTrue(\Survey::model()->deleteSurvey($surveyId), 'Survey could not be deleted');
        // Already gone, so tearDownAfterClass must not try to delete it again
        self::$testSurvey = null;
    }

    /**
     * @return int Id of the most recent audit log entry, 0 if there is none
     */
    private function getLastLogId(): int
    {
        return (int) \Yii::app()->db->createCommand()
            ->select('MAX(id)')
            ->from('{{auditlog_log}}')
            ->queryScalar();
    }

    /**
     * @param int $lastLogId
     * @return array[]
     */
    private function getLogRowsAfter(int $lastLogId): array
    {
        return \Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{auditlog_log}}')
            ->where('id > :id', [':id' => $lastLogId])
            ->order('id')
            ->queryAll();
    }
}
