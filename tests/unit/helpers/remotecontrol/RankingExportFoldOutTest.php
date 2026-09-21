<?php

namespace ls\tests\unit\helpers\remotecontrol;

use Plugin;
use Question;
use SurveyActivator;
use SurveyDynamic;
use Yii;

/**
 * Regression test for bug #20713: ranking question responses are stored as a
 * single JSON array column per question (the ranked list of subquestion
 * codes, ordered by rank), but the "Export responses" screen only ever
 * offered that single combined column, so exports showed one cell holding
 * the raw JSON instead of one column per rank (as versions before the JSON
 * storage migration did).
 *
 * The fix folds the combined column out into one column per rank position
 * during export, sized to the highest number of items actually ranked across
 * the exported responses (not the question's total subquestion count, since
 * a respondent may rank fewer items than exist).
 *
 * @group services
 */
class RankingExportFoldOutTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.admin.export.*', true);
        Yii::import('application.libraries.BigData', true);
    }

    /**
     * @return void
     */
    public function testRankingExportFoldsOutIntoDynamicallySizedColumns()
    {
        $this->activateAuthdbExportPlugin();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss');

        $activator = new SurveyActivator(self::$testSurvey);
        $activator->activate();
        Yii::app()->db->schema->refresh();

        $rankingQuestion = \Question::model()->findByAttributes([
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

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        // "Full answer" export: resolved subquestion text, one column per rank used.
        $result = $this->handler->export_responses(
            $sessionKey,
            self::$surveyId,
            'csv',
            'en',
            'all',
            'code',
            'long',
            null,
            null,
            [$rankFieldName]
        );
        $this->assertNotNull($result);
        $rows = $this->parseCsv(file_get_contents($result->fileName));

        $this->assertCount(3, $rows[0], 'Ranking question should fold out into one column per rank actually used (3), not per defined item (4).');

        $rowA = $rows[1];
        $this->assertSame($codeToText[$orderA[0]], $rowA[0]);
        $this->assertSame($codeToText[$orderA[1]], $rowA[1]);
        $this->assertSame($codeToText[$orderA[2]], $rowA[2]);

        $rowB = $rows[2];
        $this->assertSame($codeToText[$orderB[0]], $rowB[0]);
        $this->assertSame($codeToText[$orderB[1]], $rowB[1]);
        $this->assertSame('', $rowB[2], 'Response B only ranked 2 items; the 3rd rank column should be blank for it.');

        // "Answer codes" export: same fold-out, but raw codes instead of resolved text.
        $shortResult = $this->handler->export_responses(
            $sessionKey,
            self::$surveyId,
            'csv',
            'en',
            'all',
            'code',
            'short',
            null,
            null,
            [$rankFieldName]
        );
        $shortRows = $this->parseCsv(file_get_contents($shortResult->fileName));
        $this->assertCount(3, $shortRows[0]);
        $this->assertSame($orderA, array_values($shortRows[1]));
        $this->assertSame([$orderB[0], $orderB[1], ''], array_values($shortRows[2]));

        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /**
     * Ensures the core plugin that registers the CSV/JSON/XLS/... export
     * writers (see application/core/plugins/Authdb/Authdb.php::listExportPlugins())
     * is active, matching the setup RemoteControlExportResponsesTest uses.
     *
     * @return void
     */
    private function activateAuthdbExportPlugin()
    {
        $dbo = Yii::app()->getDb();
        $plugin = Plugin::model()->findByAttributes(['name' => 'Authdb']);
        if (!$plugin) {
            $plugin = new Plugin();
            $plugin->name = 'Authdb';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
        $dbo->createCommand('DELETE FROM {{failed_login_attempts}}')->execute();
    }

    /**
     * Parses the semicolon-delimited, double-quoted CSV produced by CsvWriter
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
            return str_getcsv($line, ';');
        }, $lines));
    }
}
