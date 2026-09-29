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
        \Yii::app()->user->setId(1);
    }

    /**
     * Leave AuditLog as it was found, so it does not log (or fail) in other test classes.
     */
    public static function tearDownAfterClass(): void
    {
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
     * If the audit entry cannot be written, the survey must not be deleted:
     * the entry is written before the survey row is removed, and the database error aborts the deletion.
     */
    public function testSurveyIsNotDeletedWhenAuditEntryCannotBeWritten()
    {
        $surveyId = $this->importTestSurvey();
        $db = \Yii::app()->db;
        // Load the log table schema first, so the write fails on insert like on a database failure.
        // Loading it while the table is missing would keep a broken model cached for the rest of the run.
        \PluginDynamic::model($db->tablePrefix . 'auditlog_log');
        $db->createCommand()->renameTable('{{auditlog_log}}', '{{auditlog_log_unavailable}}');
        $exception = null;
        try {
            \Survey::model()->deleteSurvey($surveyId);
        } catch (\CDbException $e) {
            $exception = $e;
        } finally {
            $db->createCommand()->renameTable('{{auditlog_log_unavailable}}', '{{auditlog_log}}');
        }

        $this->assertNotNull($exception, 'Deletion did not fail although the audit entry could not be written');
        \Survey::model()->resetCache();
        $this->assertNotNull(\Survey::model()->findByPk($surveyId), 'Survey was deleted without an audit entry');
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
