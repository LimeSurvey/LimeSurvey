<?php

namespace ls\tests;

/**
 * Deleting a survey must also delete the archived (old_*) tables created by
 * previous deactivations of that survey, together with their archived table
 * settings, but must leave the archives of any other survey untouched.
 */
class SurveyDeleteArchivedTablesTest extends TestBaseClass
{
    /** @var string Archive date suffix used for the fixture tables */
    private const ARCHIVE_DATE = '20260101120000';

    /** Imports the survey fixture. */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->session['loginID'] = 1;
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss');
    }

    /**
     * Verifies that all archived tables and archived table settings of the deleted survey are
     * removed, while the archive of a different survey with a similar ID is kept.
     */
    public function testDeleteSurveyDropsArchivedTables()
    {
        $db = \Yii::app()->db;
        $surveyId = self::$surveyId;
        // Another survey ID starting with the same digits, so a sloppy LIKE match would catch it
        $otherSurveyId = $surveyId . '9';

        $ownTables = [];
        foreach (['responses', 'timings', 'tokens', 'questions'] as $type) {
            $ownTables[] = $this->createArchiveTable("old_{$type}_{$surveyId}_" . self::ARCHIVE_DATE);
        }
        $otherTable = $this->createArchiveTable("old_responses_{$otherSurveyId}_" . self::ARCHIVE_DATE);

        $this->createArchivedTableSettings($surveyId, "old_responses_{$surveyId}_" . self::ARCHIVE_DATE);
        $otherSettings = $this->createArchivedTableSettings(
            (int) $otherSurveyId,
            "old_responses_{$otherSurveyId}_" . self::ARCHIVE_DATE
        );

        try {
            $archivedTableNames = self::$testSurvey->getArchivedTableNames();
            sort($archivedTableNames);
            $expectedTableNames = $ownTables;
            sort($expectedTableNames);
            $this->assertSame(
                $expectedTableNames,
                $archivedTableNames,
                'Only the archives of this survey must be found.'
            );

            $this->assertTrue(\Survey::model()->deleteSurvey($surveyId), 'Survey could not be deleted.');
            self::$testSurvey = null;
            $db->schema->refresh();

            foreach ($ownTables as $tableName) {
                $this->assertNull($db->schema->getTable($tableName), "Archived table $tableName was not deleted.");
            }
            $this->assertNotNull($db->schema->getTable($otherTable), 'Archived table of another survey was deleted.');

            $this->assertFalse(
                \ArchivedTableSettings::model()->exists('survey_id = :sid', [':sid' => $surveyId]),
                'Archived table settings of the deleted survey were not deleted.'
            );
            $this->assertNotNull(
                \ArchivedTableSettings::model()->findByPk($otherSettings->id),
                'Archived table settings of another survey were deleted.'
            );
        } finally {
            $db->schema->refresh();
            foreach (array_merge($ownTables, [$otherTable]) as $tableName) {
                if ($db->schema->getTable($tableName)) {
                    $db->createCommand()->dropTable($tableName);
                }
            }
            \ArchivedTableSettings::model()->deleteAllByAttributes(['survey_id' => [$surveyId, (int) $otherSurveyId]]);
        }
    }

    /**
     * Creates a minimal archive table.
     *
     * @param string $tableName Table name without prefix
     * @return string Full (prefixed) table name
     */
    private function createArchiveTable($tableName)
    {
        $db = \Yii::app()->db;
        $db->createCommand()->createTable("{{{$tableName}}}", ['id' => 'pk']);
        return $db->tablePrefix . $tableName;
    }

    /**
     * Saves an archived table settings row.
     *
     * @param int $surveyId
     * @param string $tableName Table name without prefix
     * @return \ArchivedTableSettings
     */
    private function createArchivedTableSettings($surveyId, $tableName)
    {
        $settings = new \ArchivedTableSettings();
        $settings->survey_id = $surveyId;
        $settings->user_id = 1;
        $settings->tbl_name = $tableName;
        $settings->tbl_type = 'response';
        $settings->created = date('Y-m-d H:i:s');
        $settings->properties = json_encode([]);
        $this->assertTrue(
            $settings->save(),
            'Could not save archived table settings: ' . json_encode($settings->errors)
        );
        return $settings;
    }
}
