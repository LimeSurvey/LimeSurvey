<?php

namespace ls\tests;

use ArchivedTableSettings;
use LimeSurvey\Models\Services\SurveyArchiveService;
use LimeSurvey\Models\Services\SurveyDeactivate;

/**
 * Tests for the archive management service (list, read, alias, export and delete archived survey data).
 *
 * @group archive
 */
class SurveyArchiveServiceTest extends TestBaseClass
{
    /** @var int timestamp suffix of the archive created by deactivating the test survey */
    private static $archiveTimestamp;

    /** @var SurveyArchiveService */
    private $service;

    /**
     * Imports a survey with responses and deactivates it, so that an archive exists.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        App()->user->setId(1);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_969899_ImportResponses.lsa');

        $deactivate = new SurveyDeactivate(
            self::$testSurvey,
            \Permission::model(),
            new \SurveyDeactivator(self::$testSurvey),
            App(),
            \SurveyLink::model(),
            \SavedControl::model()
        );
        $deactivate->setArchivedResponseSettings(new ArchivedTableSettings());
        $deactivate->setArchivedTimingsSettings(new ArchivedTableSettings());
        $deactivate->setArchivedTokenSettings(new ArchivedTableSettings());
        $deactivate->deactivate(self::$surveyId, ['ok' => true], true);
        App()->db->schema->refresh();

        $archive = ArchivedTableSettings::model()->findByAttributes(
            ['survey_id' => self::$surveyId, 'tbl_type' => SurveyArchiveService::$Response_archive]
        );
        $parts = explode('_', $archive->tbl_name);
        self::$archiveTimestamp = (int) end($parts);
    }

    /**
     * Drops any archive tables left behind by the tests.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;
        foreach (['response', 'token', 'timings', 'questions'] as $archiveType) {
            $tableName = '{{' . SurveyArchiveService::buildArchiveTableName($archiveType, self::$surveyId, self::$archiveTimestamp) . '}}';
            if (tableExists($tableName)) {
                App()->db->createCommand()->dropTable($tableName);
            }
        }
        ArchivedTableSettings::model()->deleteAllByAttributes(['survey_id' => self::$surveyId]);
        parent::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::app()->session['loginID'] = 1;
        $this->service = new SurveyArchiveService(\Survey::model(), \Permission::model(), App());
    }

    /**
     * Archive table names follow the naming used on survey deactivation.
     *
     * @return void
     */
    public function testBuildArchiveTableName()
    {
        $this->assertSame('old_responses_12_20250101120000', SurveyArchiveService::buildArchiveTableName('response', 12, 20250101120000));
        $this->assertSame('old_tokens_12_20250101120000', SurveyArchiveService::buildArchiveTableName('token', 12, 20250101120000));
        $this->assertSame('old_timings_12_20250101120000', SurveyArchiveService::buildArchiveTableName('timings', 12, 20250101120000));
        $this->assertSame('old_questions_12_20250101120000', SurveyArchiveService::buildArchiveTableName('questions', 12, 20250101120000));

        $this->expectException(\InvalidArgumentException::class);
        SurveyArchiveService::buildArchiveTableName('unknown', 12, 20250101120000);
    }

    /**
     * The archive created on deactivation is found and returned with its responses.
     *
     * @return void
     */
    public function testGetResponseArchiveData()
    {
        $this->assertTrue($this->service->doesArchiveExists(self::$surveyId, self::$archiveTimestamp, SurveyArchiveService::$Response_archive));
        $this->assertFalse($this->service->doesArchiveExists(self::$surveyId, self::$archiveTimestamp + 1, SurveyArchiveService::$Response_archive));

        $result = $this->service->getResponseArchiveData(self::$surveyId, self::$archiveTimestamp);
        $archiveCount = (int) App()->db->createCommand()
            ->select('COUNT(*)')
            ->from($this->responseArchiveTable())
            ->queryScalar();

        $this->assertGreaterThan(0, $archiveCount);
        $this->assertSame($archiveCount, (int) $result['meta']['totalItems']);
        $this->assertNotEmpty($result['data']);
        $this->assertArrayHasKey('fieldDetails', $result['data'][0]);
    }

    /**
     * Filtering on a column that does not exist in the archive table is rejected.
     *
     * @return void
     */
    public function testUnknownFilterFieldIsRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getResponseArchiveData(
            self::$surveyId,
            self::$archiveTimestamp,
            ['filters' => ['not_a_column' => 'x']]
        );
    }

    /**
     * Sorting on a column that does not exist in the archive table is rejected.
     *
     * @return void
     */
    public function testUnknownSortFieldIsRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getResponseArchiveData(
            self::$surveyId,
            self::$archiveTimestamp,
            ['sort' => ['attribute' => 'not_a_column', 'direction' => 'desc']]
        );
    }

    /**
     * Sorting and filtering on existing columns works.
     *
     * @return void
     */
    public function testSortAndFilterOnExistingColumn()
    {
        $result = $this->service->getResponseArchiveData(
            self::$surveyId,
            self::$archiveTimestamp,
            ['sort' => ['attribute' => 'id', 'direction' => 'desc'], 'filters' => ['startlanguage' => 'en']]
        );
        $ids = array_column($result['data'], 'id');
        $sortedIds = $ids;
        rsort($sortedIds);
        $this->assertSame($sortedIds, $ids);
    }

    /**
     * An alias can be set on an archive and is read back.
     *
     * @return void
     */
    public function testArchiveAlias()
    {
        $this->assertTrue($this->service->updateArchiveAlias(self::$surveyId, self::$archiveTimestamp, 'First wave'));
        $this->assertSame('First wave', $this->service->getArchiveAlias(self::$surveyId, self::$archiveTimestamp));
    }

    /**
     * An alias can be cleared by setting it to an empty string.
     *
     * @return void
     */
    public function testArchiveAliasCanBeCleared()
    {
        $this->service->updateArchiveAlias(self::$surveyId, self::$archiveTimestamp, 'Second wave');
        $this->assertTrue($this->service->updateArchiveAlias(self::$surveyId, self::$archiveTimestamp, ''));
        $this->assertSame('', $this->service->getArchiveAlias(self::$surveyId, self::$archiveTimestamp));
    }

    /**
     * Subquestion titles are built for single-scale, dual-scale and comment fields.
     *
     * @return void
     */
    public function testBuildSubQuestionTitle()
    {
        $this->assertSame('', SurveyArchiveService::buildSubQuestionTitle([]));
        $this->assertSame('Row 1', SurveyArchiveService::buildSubQuestionTitle(['subquestion' => 'Row 1']));
        $this->assertSame('Row 1 - Column A', SurveyArchiveService::buildSubQuestionTitle(['subquestion1' => 'Row 1', 'subquestion2' => 'Column A']));
        $this->assertSame('Option 1 - Comment', SurveyArchiveService::buildSubQuestionTitle(['subquestion' => 'Option 1', 'subquestion1' => 'Comment']));
        $this->assertSame('0', SurveyArchiveService::buildSubQuestionTitle(['subquestion' => '0']));
    }

    /**
     * Exported CSV values are quoted, embedded quotes are doubled and leading formula characters are masked.
     *
     * @return void
     */
    public function testExportResponsesEscapesValues()
    {
        $firstId = (int) App()->db->createCommand()
            ->select('MIN(id)')
            ->from($this->responseArchiveTable())
            ->queryScalar();

        App()->db->createCommand()->update($this->responseArchiveTable(), ['startlanguage' => '=x'], 'id = :id', [':id' => $firstId]);
        $csv = $this->exportResponses();
        $this->assertStringContainsString('"\'=x"', $csv);

        App()->db->createCommand()->update($this->responseArchiveTable(), ['startlanguage' => 'a"b'], 'id = :id', [':id' => $firstId]);
        $csv = $this->exportResponses();
        $this->assertStringContainsString('"a""b"', $csv);

        App()->db->createCommand()->update($this->responseArchiveTable(), ['startlanguage' => '-5'], 'id = :id', [':id' => $firstId]);
        $csv = $this->exportResponses();
        $this->assertStringContainsString('"-5"', $csv);
        $this->assertStringNotContainsString('"\'-5"', $csv);

        $lines = array_filter(explode("\n", $csv));
        $this->assertStringContainsString('"id"', $lines[0]);
    }

    /**
     * Deleting the response archive also removes the questions snapshot and the archive settings entry.
     *
     * @return void
     */
    public function testDeleteResponseArchive()
    {
        $questionsTable = '{{' . SurveyArchiveService::buildArchiveTableName('questions', self::$surveyId, self::$archiveTimestamp) . '}}';
        $this->assertTrue(tableExists($this->responseArchiveTable()));
        $this->assertTrue(tableExists($questionsTable));

        $this->service->deleteArchiveData(self::$surveyId, self::$archiveTimestamp, [SurveyArchiveService::$Response_archive]);
        App()->db->schema->refresh();

        $this->assertFalse(tableExists($this->responseArchiveTable()));
        $this->assertFalse(tableExists($questionsTable));
        $this->assertNull(ArchivedTableSettings::getArchiveForTimestamp(self::$surveyId, self::$archiveTimestamp));
    }

    /**
     * Unknown archive types are rejected before anything is deleted.
     *
     * @return void
     */
    public function testDeleteUnknownArchiveTypeIsRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->deleteArchiveData(self::$surveyId, self::$archiveTimestamp, ['unknown']);
    }

    /**
     * @return string the prefixed-placeholder name of the response archive table
     */
    private function responseArchiveTable(): string
    {
        return '{{' . SurveyArchiveService::buildArchiveTableName('response', self::$surveyId, self::$archiveTimestamp) . '}}';
    }

    /**
     * @return string the streamed CSV export of the response archive
     */
    private function exportResponses(): string
    {
        ob_start();
        $this->service->exportResponsesAsStream(self::$surveyId, self::$archiveTimestamp);
        return (string) ob_get_clean();
    }
}
