<?php

namespace ls\tests\unit\helpers\admin\export;

use FormattingOptions;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\ExportSurveyResultsService as ApiExportService;
use LimeSurvey\Models\Services\SurveyAnswerCache;
use ls\tests\TestBaseClass;
use Plugin;
use Question;
use QuestionL10n;
use Survey;
use SurveyActivator;
use SurveyDynamic;
use Yii;

/**
 * Regression test for bug #20713: ranking answers are stored as one JSON
 * array column per question, but exports must show one column per rank like
 * in LimeSurvey 6.
 *
 * Each test imports the fixture under its own survey ID: the import otherwise
 * keeps the ID from the file, and getSubQuestions() caches subquestions per
 * survey ID for the whole PHP process, so any earlier import of the same file
 * would leave stale subquestions behind.
 *
 * @group services
 */
class RankingExportTest extends TestBaseClass
{
    /** @var string Response column of the ranking question */
    private $rankFieldName;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.admin.export.*', true);
        Yii::app()->loadHelper('export');
        Yii::app()->loadHelper('admin.exportresults');
        // The core plugin that registers the CSV writer.
        $plugin = Plugin::model()->findByAttributes(['name' => 'Authdb']);
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
    }

    /**
     * @return void
     */
    public function testRankingIsExportedWithOneColumnPerRank()
    {
        $rankingQuestion = $this->createRankingSurvey(207130, false);
        $this->insertRankings([
            json_encode(['SQ03', 'SQ01', 'SQ04']),
            json_encode(['SQ02', 'SQ04']),
            // Invalid values must not break the export and render as empty ranks.
            '{"0":"SQ01","1":"SQ02"}',
            '[]',
            json_encode(['SQ01', 1.5, 'SQ02']),
            json_encode([['SQ01'], 'SQ02']),
            // Integer codes are accepted as strings.
            json_encode([2, 'SQ01']),
        ]);
        $emptyRow = ['', '', '', ''];

        // Classic "Export responses" screen.
        $rows = $this->classicExport('code', 'short');
        $this->assertSame(['Rank[1]', 'Rank[2]', 'Rank[3]', 'Rank[4]'], $rows[0]);
        $this->assertSame(['SQ03', 'SQ01', 'SQ04', ''], $rows[1]);
        $this->assertSame(['SQ02', 'SQ04', '', ''], $rows[2]);
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $rows[$row]);
        }
        $this->assertSame(['2', 'SQ01', '', ''], $rows[7]);

        $rows = $this->classicExport('full', 'long');
        $this->assertSame('Ranking question [Rank 1]', $rows[0][0]);
        $this->assertSame(['Third subquestion', 'Some example subquestion', 'Last subquestion', ''], $rows[1]);
        $this->assertSame(['New answer subquestion', 'Last subquestion', '', ''], $rows[2]);
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $rows[$row]);
        }
        $this->assertSame(['2', 'Some example subquestion', '', ''], $rows[7]);

        // REST API export.
        $rows = $this->restExport('short');
        $this->assertSame(['SQ03', 'SQ01', 'SQ04', ''], $rows[1]);
        $this->assertSame(['SQ02', 'SQ04', '', ''], $rows[2]);
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $rows[$row]);
        }
        $this->assertSame(['2', 'SQ01', '', ''], $rows[7]);

        $rows = $this->restExport('long');
        $this->assertSame(['Third subquestion', 'Some example subquestion', 'Last subquestion', ''], $rows[1]);
        $this->assertSame(['New answer subquestion', 'Last subquestion', '', ''], $rows[2]);
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $rows[$row]);
        }
        $this->assertSame(['2', 'Some example subquestion', '', ''], $rows[7]);

        // "Strip HTML code" applies to ranking item texts like to answer options.
        $item = Question::model()->findByAttributes(['parent_qid' => $rankingQuestion->qid, 'title' => 'SQ03']);
        QuestionL10n::model()->updateAll(
            ['question' => '<p><strong>Third</strong> subquestion</p>'],
            'qid = :qid',
            [':qid' => $item->qid]
        );
        $rows = $this->classicExport('code', 'long', '1');
        $this->assertSame('Third subquestion', $rows[1][0]);
        $rows = $this->classicExport('code', 'long', '0');
        $this->assertSame('<p><strong>Third</strong> subquestion</p>', $rows[1][0]);

        $this->deleteSurvey();
    }

    /**
     * Encrypted ranking answers are decrypted before being folded out.
     *
     * @return void
     */
    public function testEncryptedRankingIsExportedWithOneColumnPerRank()
    {
        $this->createRankingSurvey(207131, true);
        $this->insertRankings([json_encode(['SQ03', 'SQ01'])]);
        $this->assertStringNotContainsString(
            'SQ03',
            (string) SurveyDynamic::model(self::$surveyId)->findByPk(1)->getAttribute($this->rankFieldName),
            'The ranking was not stored encrypted.'
        );

        $expected = ['Third subquestion', 'Some example subquestion', '', ''];
        $this->assertSame($expected, $this->classicExport('code', 'long')[1]);
        $this->assertSame($expected, $this->restExport('long')[1]);

        $this->deleteSurvey();
    }

    /**
     * Imports the ranking fixture under the given survey ID and activates it.
     *
     * @param int $surveyId
     * @param bool $encrypted Whether the ranking question stores its answers encrypted
     * @return Question The ranking question
     */
    private function createRankingSurvey($surveyId, $encrypted)
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss', 1, $surveyId);
        $rankingQuestion = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => Question::QT_R_RANKING,
            'parent_qid' => 0,
        ]);
        if ($encrypted) {
            $rankingQuestion->encrypted = 'Y';
            $rankingQuestion->save();
        }
        $activator = new SurveyActivator(self::$testSurvey);
        $activator->activate();
        Yii::app()->db->schema->refresh();
        $this->rankFieldName = 'Q' . $rankingQuestion->qid;
        return $rankingQuestion;
    }

    /**
     * Inserts one response per given ranking column value.
     *
     * @param string[] $values Raw ranking column values
     * @return void
     */
    private function insertRankings(array $values)
    {
        foreach ($values as $value) {
            // insertRecords() encrypts the values of encrypted questions
            SurveyDynamic::model(self::$surveyId)->insertRecords([
                'startlanguage' => 'en',
                'submitdate' => date('Y-m-d H:i:s'),
                $this->rankFieldName => $value,
            ]);
        }
    }

    /**
     * Deletes the current test survey.
     *
     * @return void
     */
    private function deleteSurvey()
    {
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Runs the classic CSV export of the ranking question and returns the parsed rows.
     *
     * @param string $headingFormat
     * @param string $answerFormat
     * @param string $stripHtmlCode "1" to strip HTML from answer texts
     * @return array<int, array<int, string>>
     */
    private function classicExport($headingFormat, $answerFormat, $stripHtmlCode = '0')
    {
        $options = new FormattingOptions();
        $options->selectedColumns = [$this->rankFieldName];
        $options->responseMinRecord = 1;
        $options->responseMaxRecord = SurveyDynamic::model(self::$surveyId)->getMaxId();
        $options->responseCompletionState = 'all';
        $options->headingFormat = $headingFormat;
        $options->answerFormat = $answerFormat;
        $options->stripHtmlCode = $stripHtmlCode;
        $options->output = 'file';
        $file = (new \ExportSurveyResultsService())->exportResponses(self::$surveyId, 'en', 'csv', $options);
        $csv = file_get_contents($file);
        unlink($file);
        return $this->parseCsv($csv, ';');
    }

    /**
     * Runs the REST API CSV export and returns the rows reduced to the 4 rank columns.
     *
     * @param string $answerFormat 'short' or 'long'
     * @return array<int, array<int, string>>
     */
    private function restExport($answerFormat)
    {
        $answerCache = new SurveyAnswerCache();
        $service = new ApiExportService(
            new Survey(),
            new FilterPatcher(),
            new TransformerOutputSurveyResponses(),
            new ExportAnswerFormatter($answerCache),
            $answerCache
        );
        $result = $service->setLanguage('en')->setAnswerFormat($answerFormat)->exportResponses(self::$surveyId, 'csv');
        $rows = $this->parseCsv($result['content'], ',');
        $rankColumns = array_flip(array_keys(preg_grep('/^Ranking question \[Rank \d\]$/', $rows[0])));
        $this->assertCount(4, $rankColumns);
        return array_map(function ($row) use ($rankColumns) {
            return array_values(array_intersect_key($row, $rankColumns));
        }, $rows);
    }

    /**
     * Parses exported CSV content into rows of fields.
     *
     * @param string $csv
     * @param string $separator
     * @return array<int, array<int, string>>
     */
    private function parseCsv($csv, $separator)
    {
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $lines = array_filter(explode("\r\n", $csv), function ($line) {
            return $line !== '';
        });
        return array_values(array_map(function ($line) use ($separator) {
            return str_getcsv($line, $separator, '"', "\\");
        }, $lines));
    }
}
