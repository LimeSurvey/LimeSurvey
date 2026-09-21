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
        $firstSubQuestion = reset($subQuestions);

        // Legacy exporter: SurveyObj::getFullAnswer()
        $survey = (new SurveyDao())->loadSurveyById(self::$surveyId, 'en');

        $rankingFieldName = null;
        foreach ($survey->fieldMap as $fieldName => $field) {
            if ($field['qid'] == $rankingQuestion->qid && $field['type'] === Question::QT_R_RANKING) {
                $rankingFieldName = $fieldName;
                break;
            }
        }
        $this->assertNotNull($rankingFieldName, 'Ranking field should exist in the field map.');

        $legacyFullAnswer = $survey->getFullAnswer($rankingFieldName, $firstSubQuestion['title'], new Translator(), 'en');

        // Before the fix, $legacyFullAnswer was the raw subquestion code (e.g. "SQ01").
        $this->assertNotSame($firstSubQuestion['title'], $legacyFullAnswer);
        $this->assertSame($firstSubQuestion['question'], $legacyFullAnswer);

        // Newer namespaced exporter: ExportAnswerFormatter::formatFullAnswer()
        $answerCache = new SurveyAnswerCache();
        $formatter = new ExportAnswerFormatter($answerCache);
        $formatter->loadAnswers(self::$surveyId, 'en');

        $apiFullAnswer = $formatter->formatFullAnswer(
            $firstSubQuestion['title'],
            Question::QT_R_RANKING,
            'Rank',
            $rankingQuestion->qid
        );

        $this->assertNotSame($firstSubQuestion['title'], $apiFullAnswer);
        $this->assertSame($firstSubQuestion['question'], $apiFullAnswer);

        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
