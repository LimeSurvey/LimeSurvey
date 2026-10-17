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
 * The fixture is imported under its own survey ID: the import otherwise keeps
 * the ID from the file, and getSubQuestions() caches subquestions per survey ID
 * for the whole PHP process, so any earlier test importing the same file would
 * leave stale subquestions behind.
 *
 * @group services
 */
class RankingExportTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.admin.export.*', true);
        Yii::app()->loadHelper('export');
        Yii::app()->loadHelper('admin.exportresults');
    }

    /**
     * @return void
     */
    public function testRankingIsExportedWithOneColumnPerRank()
    {
        // The core plugin that registers the CSV writer.
        $plugin = Plugin::model()->findByAttributes(['name' => 'Authdb']);
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss', 1, 207130);
        $activator = new SurveyActivator(self::$testSurvey);
        $activator->activate();
        Yii::app()->db->schema->refresh();

        $rankingQuestion = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => Question::QT_R_RANKING,
            'parent_qid' => 0,
        ]);
        $rankFieldName = 'Q' . $rankingQuestion->qid;

        $responses = [
            json_encode(['SQ03', 'SQ01', 'SQ04']),
            json_encode(['SQ02', 'SQ04']),
            // Invalid values must not break the export and render as empty ranks.
            '{"0":"SQ01","1":"SQ02"}',
            '[]',
            json_encode(['SQ01', 1.5, 'SQ02']),
            json_encode([['SQ01'], 'SQ02']),
            // Integer codes are accepted as strings.
            json_encode([2, 'SQ01']),
        ];
        foreach ($responses as $value) {
            SurveyDynamic::model(self::$surveyId)->insertRecords([
                'startlanguage' => 'en',
                'submitdate' => date('Y-m-d H:i:s'),
                $rankFieldName => $value,
            ]);
        }
        $emptyRow = ['', '', '', ''];

        // Classic "Export responses" screen.
        $rows = $this->classicExport([$rankFieldName], 'code', 'short');
        $this->assertSame(['Rank[1]', 'Rank[2]', 'Rank[3]', 'Rank[4]'], $rows[0]);
        $this->assertSame(['SQ03', 'SQ01', 'SQ04', ''], $rows[1]);
        $this->assertSame(['SQ02', 'SQ04', '', ''], $rows[2]);
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $rows[$row]);
        }
        $this->assertSame(['2', 'SQ01', '', ''], $rows[7]);

        $rows = $this->classicExport([$rankFieldName], 'full', 'long');
        $this->assertSame('Ranking question [Rank 1]', $rows[0][0]);
        $this->assertSame(['Third subquestion', 'Some example subquestion', 'Last subquestion', ''], $rows[1]);
        $this->assertSame(['New answer subquestion', 'Last subquestion', '', ''], $rows[2]);
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $rows[$row]);
        }
        $this->assertSame(['2', 'Some example subquestion', '', ''], $rows[7]);

        // REST API export.
        $answerCache = new SurveyAnswerCache();
        $apiService = new ApiExportService(
            new Survey(),
            new FilterPatcher(),
            new TransformerOutputSurveyResponses(),
            new ExportAnswerFormatter($answerCache),
            $answerCache
        );
        $result = $apiService->setLanguage('en')->exportResponses(self::$surveyId, 'csv');
        $rows = $this->parseCsv($result['content'], ',');
        $rankColumns = array_keys(preg_grep('/^Ranking question \[Rank \d\]$/', $rows[0]));
        $this->assertCount(4, $rankColumns);
        $pick = function (array $row) use ($rankColumns) {
            return array_values(array_intersect_key($row, array_flip($rankColumns)));
        };
        $this->assertSame(['Third subquestion', 'Some example subquestion', 'Last subquestion', ''], $pick($rows[1]));
        $this->assertSame(['New answer subquestion', 'Last subquestion', '', ''], $pick($rows[2]));
        for ($row = 3; $row <= 6; $row++) {
            $this->assertSame($emptyRow, $pick($rows[$row]));
        }
        $this->assertSame(['2', 'Some example subquestion', '', ''], $pick($rows[7]));

        // "Strip HTML code" applies to ranking item texts like to answer options.
        $item = Question::model()->findByAttributes(['parent_qid' => $rankingQuestion->qid, 'title' => 'SQ03']);
        QuestionL10n::model()->updateAll(
            ['question' => '<p><strong>Third</strong> subquestion</p>'],
            'qid = :qid',
            [':qid' => $item->qid]
        );
        $rows = $this->classicExport([$rankFieldName], 'code', 'long', '1');
        $this->assertSame('Third subquestion', $rows[1][0]);
        $rows = $this->classicExport([$rankFieldName], 'code', 'long', '0');
        $this->assertSame('<p><strong>Third</strong> subquestion</p>', $rows[1][0]);
    }

    /**
     * Runs the classic CSV export and returns the parsed rows.
     *
     * @param string[] $columns Selected columns
     * @param string $headingFormat
     * @param string $answerFormat
     * @param string $stripHtmlCode "1" to strip HTML from answer texts
     * @return array<int, array<int, string>>
     */
    private function classicExport(array $columns, $headingFormat, $answerFormat, $stripHtmlCode = '0')
    {
        $options = new FormattingOptions();
        $options->selectedColumns = $columns;
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
            return str_getcsv($line, $separator);
        }, $lines));
    }
}
