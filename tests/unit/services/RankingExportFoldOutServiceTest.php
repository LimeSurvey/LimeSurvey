<?php

namespace ls\tests\unit\services;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use LimeSurvey\Models\Services\SurveyAnswerCache;
use ls\tests\TestBaseClass;
use Question;
use Survey;
use SurveyActivator;
use SurveyDynamic;
use Yii;

/**
 * Regression test for bug #20713 on the newer namespaced/API exporter
 * (LimeSurvey\Models\Services\ExportSurveyResultsService, used by the REST
 * API's response export command, application/libraries/Api/Command/V1/SurveyResponsesExport.php).
 *
 * Same underlying issue and fix as the classic "Export responses" screen
 * (see tests/unit/helpers/remotecontrol/RankingExportFoldOutTest.php): ranking
 * answers are stored as a single JSON array column per question, so this
 * folds that out into one column per rank actually used in the data.
 *
 * @group services
 */
class RankingExportFoldOutServiceTest extends TestBaseClass
{
    /**
     * @return void
     */
    public function testRankingExportFoldsOutIntoDynamicallySizedColumns()
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss');

        $activator = new SurveyActivator(self::$testSurvey);
        $activator->activate();
        Yii::app()->db->schema->refresh();

        $rankingQuestion = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => Question::QT_R_RANKING,
            'parent_qid' => 0,
        ]);
        $this->assertNotNull($rankingQuestion, 'Fixture should contain a ranking question.');

        $subQuestions = getSubQuestions(self::$surveyId, $rankingQuestion->qid, 'en');
        $this->assertCount(4, $subQuestions, 'Fixture is expected to define 4 ranking items.');
        $codes = array_column($subQuestions, 'title');
        $texts = array_column($subQuestions, 'question');
        $codeToText = array_combine($codes, $texts);

        $rankFieldName = 'Q' . $rankingQuestion->qid;

        // Response A ranks 3 of the 4 possible items; response B ranks only 2.
        // The highest used (3) is lower than the question's total item count (4),
        // proving the column count comes from the data, not the question definition.
        $orderA = [$codes[2], $codes[0], $codes[3]];
        $orderB = [$codes[1], $codes[3]];

        SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'submitdate' => date('Y-m-d H:i:s'),
            $rankFieldName => json_encode($orderA),
        ]);
        SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'submitdate' => date('Y-m-d H:i:s'),
            $rankFieldName => json_encode($orderB),
        ]);

        $answerCache = new SurveyAnswerCache();
        $service = new ExportSurveyResultsService(
            new Survey(),
            new FilterPatcher(),
            new TransformerOutputSurveyResponses(),
            new ExportAnswerFormatter($answerCache),
            $answerCache
        );

        $result = $service->setLanguage('en')->exportResponses(self::$surveyId, 'csv');
        $rows = $this->parseCsv($result['content']);

        // This exporter has no column selection: it always exports every
        // question, so the ranking columns aren't necessarily at a fixed
        // position (other questions' own subquestion columns surround them).
        // Locate them by their "<question text> [Rank N]" heading instead.
        $rankColumnIndexes = [];
        foreach ($rows[0] as $index => $header) {
            if (preg_match('/\[Rank \d+\]$/', $header)) {
                $rankColumnIndexes[] = $index;
            }
        }
        $this->assertCount(3, $rankColumnIndexes, 'Ranking question should fold out into one column per rank actually used (3), not per defined item (4).');

        $rowA = array_map(function ($index) use ($rows) {
            return $rows[1][$index];
        }, $rankColumnIndexes);
        $rowB = array_map(function ($index) use ($rows) {
            return $rows[2][$index];
        }, $rankColumnIndexes);

        $this->assertSame($codeToText[$orderA[0]], $rowA[0]);
        $this->assertSame($codeToText[$orderA[1]], $rowA[1]);
        $this->assertSame($codeToText[$orderA[2]], $rowA[2]);

        $this->assertSame($codeToText[$orderB[0]], $rowB[0]);
        $this->assertSame($codeToText[$orderB[1]], $rowB[1]);
        $this->assertSame('', $rowB[2], 'Response B only ranked 2 items; the 3rd rank column should be blank for it.');

        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Parses the comma-delimited, double-quoted CSV produced by CsvExportWriter
     * into an array of rows of fields.
     *
     * @param string $csv
     * @return array<int, array<int, string>>
     */
    private function parseCsv($csv)
    {
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $lines = array_filter(explode("\r\n", $csv), function ($line) {
            return $line !== '';
        });
        return array_values(array_map(function ($line) {
            return str_getcsv($line, ',');
        }, $lines));
    }
}
