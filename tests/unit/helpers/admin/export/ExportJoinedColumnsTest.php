<?php

namespace ls\tests\unit\helpers\admin\export;

use FormattingOptions;
use ls\tests\TestBaseClass;
use Plugin;
use SurveyActivator;
use SurveyDao;
use SurveyDynamic;
use Token;
use Yii;

/**
 * Regression test for bug #19104: the response export joined the participant and timings tables,
 * which exceeded the maximum number of columns in a query on PostgreSQL for surveys with many
 * questions. Their values are now loaded with separate queries.
 *
 * @group services
 */
class ExportJoinedColumnsTest extends TestBaseClass
{
    /** @var string Response column of the text question */
    private static $textFieldName;

    /** @var int Id of the test response */
    private static $responseId;

    /**
     * Imports a survey with timings, adds a participant table, activates it and adds a response.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.admin.export.*', true);
        Yii::app()->loadHelper('export');
        Yii::app()->loadHelper('admin.exportresults');
        // The core plugin that registers the CSV writer.
        $plugin = Plugin::model()->findByAttributes(['name' => 'Authdb']);
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);

        // Own survey ID: other tests import this fixture too, and models like SurveyDynamic are
        // cached per survey ID for the whole PHP process, so they would see this survey's token column
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_917744.lss', 1, 191040);
        Token::createTable(self::$surveyId);
        (new SurveyActivator(self::$testSurvey))->activate();
        Yii::app()->db->schema->refresh();

        $participant = Token::create(self::$surveyId);
        $participant->firstname = 'Ana';
        $participant->lastname = 'Smith';
        $participant->token = 'tok19104';
        $participant->save();

        $textQuestion = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'dummy']);
        self::$textFieldName = 'Q' . $textQuestion->qid;
        self::$responseId = SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'startdate' => date('Y-m-d H:i:s'),
            'datestamp' => date('Y-m-d H:i:s'),
            'submitdate' => date('Y-m-d H:i:s'),
            'token' => 'tok19104',
            self::$textFieldName => 'Some text',
        ]);
        switchMSSQLIdentityInsert('timings_' . self::$surveyId, true);
        Yii::app()->db->createCommand()->insert(self::$testSurvey->timingsTableName, [
            'id' => self::$responseId,
            'interviewtime' => 42,
        ]);
        switchMSSQLIdentityInsert('timings_' . self::$surveyId, false);
    }

    /**
     * The results query only reads the response table, it does not join the participant and timings tables.
     *
     * @return void
     */
    public function testResultsQueryDoesNotJoinOtherTables()
    {
        $dao = new SurveyDao();
        $survey = $dao->loadSurveyById(self::$surveyId, 'en');
        $command = $dao->loadSurveyResults($survey, 1, PHP_INT_MAX, '', 'all', [self::$textFieldName, 'firstname', 'interviewtime']);
        $this->assertEmpty($command->getJoin());
        $select = array_map(function ($column) {
            return str_replace(['"', '`', '[', ']'], '', trim($column));
        }, explode(',', $command->getSelect()));
        $table = self::$testSurvey->responsesTableName;
        $this->assertEqualsCanonicalizing([$table . '.id', $table . '.token', $table . '.' . self::$textFieldName], $select);
    }

    /**
     * Only the selected participant and timing columns are added to the responses.
     *
     * @return void
     */
    public function testOnlySelectedJoinedValuesAreAdded()
    {
        $rows = $this->addJoinedResults([self::$textFieldName, 'firstname', 'interviewtime']);
        $this->assertSame(['id', 'token', 'firstname', 'interviewtime'], array_keys($rows[0]));
        $this->assertSame('Ana', $rows[0]['firstname']);
        $this->assertEquals(42, $rows[0]['interviewtime']);
        // A response without participant and timings gets empty values
        $this->assertSame(['id' => 0, 'token' => 'unknown', 'firstname' => null, 'interviewtime' => null], $rows[1]);
    }

    /**
     * Without a field selection, all participant and timing columns are added.
     *
     * @return void
     */
    public function testAllJoinedValuesAreAddedWithoutFieldSelection()
    {
        $rows = $this->addJoinedResults([]);
        $this->assertSame('Smith', $rows[0]['lastname']);
        $this->assertEquals(42, $rows[0]['interviewtime']);
        $this->assertArrayHasKey('tid', $rows[0]);
        $this->assertNotEmpty(preg_grep('/^G\d+time$/', array_keys($rows[0])));
    }

    /**
     * The export still contains the values of the selected participant and timing columns.
     *
     * @return void
     */
    public function testExportContainsSelectedJoinedValues()
    {
        $options = new FormattingOptions();
        $options->selectedColumns = [self::$textFieldName, 'firstname', 'interviewtime'];
        $options->responseMinRecord = 1;
        $options->responseMaxRecord = SurveyDynamic::model(self::$surveyId)->getMaxId();
        $options->responseCompletionState = 'all';
        $options->headingFormat = 'code';
        $options->answerFormat = 'short';
        $options->output = 'file';
        $file = (new \ExportSurveyResultsService())->exportResponses(self::$surveyId, 'en', 'csv', $options);
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($file));
        unlink($file);
        $rows = array_map(function ($line) {
            return str_getcsv($line, ';', '"', '\\');
        }, array_values(array_filter(explode("\r\n", $csv))));
        $this->assertSame(['dummy', 'firstname', 'interviewtime'], $rows[0]);
        $this->assertSame(['Some text', 'Ana'], array_slice($rows[1], 0, 2));
        // The timing is a float column, MSSQL returns it as "42.0"
        $this->assertEquals(42, (float) $rows[1][2]);
    }

    /**
     * Adds the participant and timing values to the test response and a response without both.
     *
     * @param string[] $fields
     * @return array[]
     */
    private function addJoinedResults(array $fields)
    {
        $dao = new SurveyDao();
        $survey = $dao->loadSurveyById(self::$surveyId, 'en');
        return $dao->addJoinedResults($survey, [
            ['id' => (int) self::$responseId, 'token' => 'tok19104'],
            ['id' => 0, 'token' => 'unknown'],
        ], $fields);
    }
}
