<?php

namespace ls\tests\unit\helpers\admin\export;

use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\SurveyAnswerCache;
use ls\tests\TestBaseClass;
use Question;
use SurveyDao;
use Translator;

/**
 * Regression test for bug #20713: exporting responses with the "Full answer"
 * option selected showed only the raw subquestion code for ranking
 * questions instead of the subquestion text.
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
     * Covers both the legacy admin export (SurveyObj::getFullAnswer, used by the
     * CSV/Excel/PDF/Doc "Export responses" screen) and the newer namespaced
     * exporter (ExportAnswerFormatter::formatFullAnswer, used by the API response
     * export command), since both resolve ranking answer codes the same way.
     *
     * Ranking answers are stored as a single JSON array column per question
     * (e.g. "Q123"), not one column per rank position: the column value is the
     * ranked list of subquestion codes, ordered by rank (index 0 = rank 1).
     * That's the only field the export column picker offers for a ranking
     * question (application/controllers/admin/Export.php filters out the
     * per-rank "_S" fieldmap entries), so it's the only input the fix needs
     * to handle.
     *
     * @return void
     */
    public function testFullAnswerExportReturnsSubquestionTextForRanking()
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
        $this->assertNotEmpty($subQuestions, 'Ranking question should have subquestions after import.');
        $this->assertGreaterThanOrEqual(2, count($subQuestions), 'Need at least 2 ranking items to prove order is preserved.');

        // Rank the items in reverse sortorder, to prove the resolved text is
        // returned in rank order rather than e.g. subquestion sortorder.
        $rankedSubQuestions = array_reverse(array_values($subQuestions));
        $rankedCodes = array_column($rankedSubQuestions, 'title');
        $expectedTexts = [];
        foreach ($rankedSubQuestions as $index => $subQuestion) {
            $expectedTexts[] = ($index + 1) . ': ' . $subQuestion['question'];
        }
        $expectedFullAnswer = implode(', ', $expectedTexts);

        $rankedCodesJson = json_encode($rankedCodes);

        // The export column picker only ever offers the parent "Q{qid}" field
        // for a ranking question (see application/controllers/admin/Export.php),
        // which is also the only fieldmap entry with an empty suffix.
        $rankingFieldName = 'Q' . $rankingQuestion->qid;
        $this->assertArrayHasKey($rankingFieldName, (new SurveyDao())->loadSurveyById(self::$surveyId, 'en')->fieldMap);

        // Legacy exporter: SurveyObj::getFullAnswer()
        $survey = (new SurveyDao())->loadSurveyById(self::$surveyId, 'en');

        $legacyFullAnswer = $survey->getFullAnswer($rankingFieldName, $rankedCodesJson, new Translator(), 'en');

        // Before the fix, $legacyFullAnswer was the raw JSON array string (e.g. '["SQ04","SQ03","SQ02","SQ01"]').
        $this->assertNotSame($rankedCodesJson, $legacyFullAnswer);
        $this->assertSame($expectedFullAnswer, $legacyFullAnswer);

        // Edge cases: null, empty string, and invalid/unresolvable JSON should
        // pass through unchanged rather than error.
        $this->assertNull($survey->getFullAnswer($rankingFieldName, null, new Translator(), 'en'));
        $this->assertSame('', $survey->getFullAnswer($rankingFieldName, '', new Translator(), 'en'));
        $this->assertSame('NOTJSON', $survey->getFullAnswer($rankingFieldName, 'NOTJSON', new Translator(), 'en'));

        // Newer namespaced exporter: ExportAnswerFormatter::formatFullAnswer()
        $answerCache = new SurveyAnswerCache();
        $formatter = new ExportAnswerFormatter($answerCache);
        $formatter->loadAnswers(self::$surveyId, 'en');

        $apiFullAnswer = $formatter->formatFullAnswer(
            $rankedCodesJson,
            Question::QT_R_RANKING,
            $rankingFieldName,
            $rankingQuestion->qid
        );

        $this->assertNotSame($rankedCodesJson, $apiFullAnswer);
        $this->assertSame($expectedFullAnswer, $apiFullAnswer);

        $this->assertNull($formatter->formatFullAnswer(null, Question::QT_R_RANKING, $rankingFieldName, $rankingQuestion->qid));
        $this->assertSame('', $formatter->formatFullAnswer('', Question::QT_R_RANKING, $rankingFieldName, $rankingQuestion->qid));
        $this->assertSame(
            'NOTJSON',
            $formatter->formatFullAnswer('NOTJSON', Question::QT_R_RANKING, $rankingFieldName, $rankingQuestion->qid)
        );

        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
