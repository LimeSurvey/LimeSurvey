<?php

namespace ls\tests\controllers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Importing responses from a deactivated survey table must only accept
 * the archived response tables of the survey itself as source (#20848).
 */
class DataEntryImportSourceTableTest extends TestBaseClass
{
    /** @var string Timestamp part of the archive table names */
    private const ARCHIVE_TIMESTAMP = '20200101000000';

    /** @var DummyController */
    private $dummyController;

    /** @var \DataEntry */
    private $controller;

    /** @var string[] Names (with {{}}) of the tables created by the test */
    private $createdTables = [];

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // The archive holds an active survey, so the responses table exists
        static::importSurvey(self::$surveysFolder . '/limesurvey_survey_969899_ImportResponses.lsa');
    }

    /**
     * Creates the controller, an archived response table of the test survey
     * and an archived response table of another survey.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.admin.DataEntry', true);
        $this->dummyController = new DummyController('dummyid');
        $this->controller = new \DataEntry($this->dummyController, 'dataentry');
        \Yii::app()->user->setId(1);
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->session['aFlashMessage'] = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->createArchive($this->ownArchive());
        $this->createArchive($this->otherSurveyArchive());
        \Yii::app()->db->schema->refresh();
    }

    /**
     * Drops the tables of the test and restores the request.
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->createdTables as $table) {
            \Yii::app()->db->createCommand()->dropTable($table);
        }
        $this->createdTables = [];
        \Yii::app()->db->schema->refresh();
        \Yii::app()->session['aFlashMessage'] = [];
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD']);
        parent::tearDown();
    }

    /**
     * Name of an archived response table of the test survey, without table prefix.
     * @return string
     */
    private function ownArchive(): string
    {
        return 'old_responses_' . self::$surveyId . '_' . self::ARCHIVE_TIMESTAMP;
    }

    /**
     * Name of an archived response table of another survey, without table prefix.
     * The ID of that survey starts with the ID of the test survey.
     * @return string
     */
    private function otherSurveyArchive(): string
    {
        return 'old_responses_' . self::$surveyId . '1_' . self::ARCHIVE_TIMESTAMP;
    }

    /**
     * Creates an archived response table with one response.
     * @param string $name Table name without prefix
     * @return void
     */
    private function createArchive(string $name): void
    {
        $db = \Yii::app()->db;
        $db->createCommand()->createTable("{{{$name}}}", [
            'id' => 'pk',
            'startlanguage' => 'string(20)',
        ]);
        $this->createdTables[] = "{{{$name}}}";
        $db->createCommand()->insert("{{{$name}}}", ['startlanguage' => 'en']);
    }

    /**
     * Returns the source table the controller accepts for the posted table name.
     * @param mixed $postedTable Posted 'table' value
     * @return string|null
     */
    private function acceptedTable($postedTable): ?string
    {
        $_POST['table'] = $postedTable;
        $method = new \ReflectionMethod(\DataEntry::class, 'getImportableResponseTable');
        return $method->invoke($this->controller, self::$surveyId);
    }

    /**
     * Number of responses of the test survey.
     * @return int
     */
    private function countResponses(): int
    {
        return (int) \Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{responses_' . self::$surveyId . '}}')
            ->queryScalar();
    }

    /**
     * The list of old response tables holds the archives of the survey only,
     * not those of a survey whose ID starts with the same digits.
     * @return void
     */
    public function testOldResponseTablesOnlyListsOwnArchives(): void
    {
        $prefix = \Yii::app()->db->tablePrefix;
        $this->assertSame(
            [$prefix . $this->ownArchive()],
            \Yii::app()->getApi()->getOldResponseTables(self::$surveyId)
        );
    }

    /**
     * An archived response table of the survey is accepted as source.
     * @return void
     */
    public function testOwnArchiveIsAccepted(): void
    {
        $table = \Yii::app()->db->tablePrefix . $this->ownArchive();
        $this->assertSame($table, $this->acceptedTable($table));
    }

    /**
     * @return array[] Callback that builds the posted value from the table prefix and the survey ID
     */
    public static function refusedTableProvider(): array
    {
        return [
            'archive of another survey' => [function ($prefix, $surveyId) {
                return $prefix . 'old_responses_' . $surveyId . '1_' . self::ARCHIVE_TIMESTAMP;
            }],
            'response table of the survey' => [function ($prefix, $surveyId) {
                return $prefix . 'responses_' . $surveyId;
            }],
            'other table' => [function ($prefix, $surveyId) {
                return $prefix . 'surveys';
            }],
            'own archive with a schema name' => [function ($prefix, $surveyId) {
                return 'otherschema.' . $prefix . 'old_responses_' . $surveyId . '_' . self::ARCHIVE_TIMESTAMP;
            }],
            'array' => [function ($prefix, $surveyId) {
                return [$prefix . 'old_responses_' . $surveyId . '_' . self::ARCHIVE_TIMESTAMP];
            }],
            'empty' => [function ($prefix, $surveyId) {
                return '';
            }],
        ];
    }

    /**
     * Any other posted table is refused as source.
     * @dataProvider refusedTableProvider
     * @param callable $postedTable Builds the posted value from the table prefix and the survey ID
     * @return void
     */
    public function testOtherTablesAreRefused(callable $postedTable): void
    {
        $this->assertNull($this->acceptedTable($postedTable((string) \Yii::app()->db->tablePrefix, self::$surveyId)));
    }

    /**
     * The name of an own archive is refused if it is posted without the table prefix.
     * @return void
     */
    public function testOwnArchiveWithoutTablePrefixIsRefused(): void
    {
        $table = $this->ownArchive();
        // On an installation without table prefix this is the full name of the archive, which is accepted
        $expected = (string) \Yii::app()->db->tablePrefix === '' ? $table : null;
        $this->assertSame($expected, $this->acceptedTable($table));
    }

    /**
     * Posting the archive of another survey imports nothing and leads back to the import form with an error.
     * @return void
     */
    public function testImportRefusesArchiveOfOtherSurvey(): void
    {
        $responsesBefore = $this->countResponses();
        $_POST['table'] = \Yii::app()->db->tablePrefix . $this->otherSurveyArchive();
        $_POST['insertmethod'] = 'renumber';

        $this->controller->import(self::$surveyId);

        $this->assertSame($responsesBefore, $this->countResponses(), 'No response must be imported');
        $this->assertSame(
            ['admin/dataentry/sa/import/surveyid/' . self::$surveyId],
            $this->dummyController->lastAction['redirect']['url'] ?? null,
            'The request must lead back to the import form'
        );
        $flashMessages = \Yii::app()->session['aFlashMessage'];
        $this->assertCount(1, $flashMessages);
        $this->assertSame('error', $flashMessages[0]['type']);
    }
}
