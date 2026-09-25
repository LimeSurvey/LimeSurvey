<?php

namespace ls\tests\unit\helpers\admin\export;

use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\SurveyAnswerCache;
use ls\tests\TestBaseClass;
use Question;
use SurveyDao;
use Translator;

/**
 * Regression tests for bug #20713 at the answer-formatting-function level.
 *
 * Both checks live in one test method: running two full survey imports in
 * the same PHPUnit process (two @test methods each calling importSurvey())
 * leaves stale LimeExpressionManager/survey-group state behind, which makes
 * the second import fail intermittently. A single import shared by both
 * assertions avoids that.
 *
 * Ranking answers are stored as a single JSON array column per question
 * (e.g. "Q123"), holding the ranked list of subquestion codes ordered by
 * rank (index 0 = rank 1). Both exporters decode that JSON up front and fold
 * it out into one column/answer entry per rank actually used in the data —
 * the classic "Export responses" screen via
 * application/helpers/admin/exportresults_helper.php::expandRankingColumns()
 * (covered end-to-end by tests/unit/helpers/remotecontrol/RankingExportFoldOutTest.php),
 * and the newer namespaced API exporter via
 * ExportSurveyResultsService::expandRankingFieldMap() /
 * TransformerOutputSurveyResponses::extractAnswers() (covered end-to-end by
 * tests/unit/services/RankingExportFoldOutServiceTest.php). By the time
 * SurveyObj::getFullAnswer() / ExportAnswerFormatter::formatFullAnswer() are
 * called for a ranking field, $answerCode/$value is therefore always a
 * single subquestion code, never the raw JSON.
 *
 * @group services
 */
class SurveyObjRankingExportTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.admin.export.*');
    }

    /**
     * @return void
     */
    public function testRankingAnswerFormattingFunctions()
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_rankingFilterHideShow.lss');

        // Ranking subquestions keep the parent's type ('R'), so filter on
        // parent_qid too or this can match a subquestion instead of the
        // top-level ranking question.
        $rankingQuestion = Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'type' => Question::QT_R_RANKING,
            'parent_qid' => 0,
        ]);
        $this->assertNotNull($rankingQuestion, 'Fixture should contain a ranking question.');

        $subQuestions = getSubQuestions(self::$surveyId, $rankingQuestion->qid, 'en');
        $this->assertGreaterThanOrEqual(2, count($subQuestions), 'Need at least 2 ranking items to prove order is preserved.');
        $firstSubQuestion = reset($subQuestions);

        // --- SurveyObj::getFullAnswer() (classic exporter) ---
        //
        // The export layer folds the base "Q{qid}" JSON column out into one
        // "Q{qid}_rank{n}" field per rank before calling getFullAnswer(); a
        // minimal fieldmap entry matching that shape is enough to unit-test
        // the resolution logic in isolation.
        $survey = (new SurveyDao())->loadSurveyById(self::$surveyId, 'en');
        $rankFieldName = 'Q' . $rankingQuestion->qid . '_rank1';
        $survey->fieldMap[$rankFieldName] = [
            'fieldname' => $rankFieldName,
            'type' => Question::QT_R_RANKING,
            'qid' => $rankingQuestion->qid,
        ];

        $fullAnswer = $survey->getFullAnswer($rankFieldName, $firstSubQuestion['title'], new Translator(), 'en');

        // Before the fix, $fullAnswer was the raw subquestion code (e.g. "SQ01").
        $this->assertNotSame($firstSubQuestion['title'], $fullAnswer);
        $this->assertSame($firstSubQuestion['question'], $fullAnswer);

        // Edge cases: null, empty string, and an unresolvable code should
        // pass through unchanged rather than error.
        $this->assertNull($survey->getFullAnswer($rankFieldName, null, new Translator(), 'en'));
        $this->assertSame('', $survey->getFullAnswer($rankFieldName, '', new Translator(), 'en'));
        $this->assertSame('NOTACODE', $survey->getFullAnswer($rankFieldName, 'NOTACODE', new Translator(), 'en'));

        // --- ExportAnswerFormatter::formatFullAnswer() (newer namespaced/API exporter) ---
        //
        // Same shape as SurveyObj::getFullAnswer() above: the export layer
        // folds the base "Q{qid}" JSON column out into one answer entry per
        // rank before formatFullAnswer() is called, so $value here is always
        // a single subquestion code.
        $answerCache = new SurveyAnswerCache();
        $formatter = new ExportAnswerFormatter($answerCache);
        $formatter->loadAnswers(self::$surveyId, 'en');

        $apiFullAnswer = $formatter->formatFullAnswer(
            $firstSubQuestion['title'],
            Question::QT_R_RANKING,
            $rankFieldName,
            $rankingQuestion->qid
        );

        // Before the fix, $apiFullAnswer was the raw subquestion code (e.g. "SQ01").
        $this->assertNotSame($firstSubQuestion['title'], $apiFullAnswer);
        $this->assertSame($firstSubQuestion['question'], $apiFullAnswer);

        $this->assertNull($formatter->formatFullAnswer(null, Question::QT_R_RANKING, $rankFieldName, $rankingQuestion->qid));
        $this->assertSame('', $formatter->formatFullAnswer('', Question::QT_R_RANKING, $rankFieldName, $rankingQuestion->qid));
        $this->assertSame(
            'NOTACODE',
            $formatter->formatFullAnswer('NOTACODE', Question::QT_R_RANKING, $rankFieldName, $rankingQuestion->qid)
        );

        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
