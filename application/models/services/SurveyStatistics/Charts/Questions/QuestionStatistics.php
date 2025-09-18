<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions;

use Exception;
use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\{MultipleChoiceProcessor,
    SingleOptionMultipleChartsProcessor,
    TextProcessor,
    RankingProcessor,
    SingleOptionProcessor,
    DualScaleProcessor
};
use PDO;
use Question;
use Yii;

class QuestionStatistics implements StatisticsChartInterface
{
    private array $factories;
    private int $surveyId;
    private string $language;

    private array $output = [];

    public function __construct()
    {
        $this->factories = [
            Question::QT_M_MULTIPLE_CHOICE => fn() => new MultipleChoiceProcessor(),
            Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS => fn() => new MultipleChoiceProcessor(),
            Question::QT_T_LONG_FREE_TEXT => fn() => new TextProcessor(),
            Question::QT_S_SHORT_FREE_TEXT => fn() => new TextProcessor(),
            Question::QT_R_RANKING => fn() => new RankingProcessor(),
            Question::QT_1_ARRAY_DUAL => fn() => new DualScaleProcessor(),

            // No statistics for these types of questions
            Question::QT_K_MULTIPLE_NUMERICAL => fn() => null,
            Question::QT_ASTERISK_EQUATION => fn() => null,
            Question::QT_D_DATE => fn() => null,
            Question::QT_VERTICAL_FILE_UPLOAD => fn() => null,
            Question::QT_U_HUGE_FREE_TEXT => fn() => null,
            Question::QT_Q_MULTIPLE_SHORT_TEXT => fn() => null,
            Question::QT_SEMICOLON_ARRAY_TEXT => fn() => null,
            Question::QT_X_TEXT_DISPLAY => fn() => null,

            // Single option with multiple graphs for each subquestion
            Question::QT_A_ARRAY_5_POINT => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_B_ARRAY_10_CHOICE_QUESTIONS => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_C_ARRAY_YES_UNCERTAIN_NO => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_F_ARRAY => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_H_ARRAY_COLUMN => fn() => new SingleOptionMultipleChartsProcessor(),

            'default' => fn() => new SingleOptionProcessor(),
        ];
    }

    public function run(int $surveyId, string $language = 'en'): array
    {
        $this->surveyId = $surveyId;
        $this->language = $language;
        $this->output = [];

        $survey = $this->fetchSurveyMetadata();
        $surveyQuestions = $survey['questions'];

        foreach ($surveyQuestions as $question) {
            $type = $question['type'];
            if (empty($this->factories[$type])) {
                $type = 'default';
            }
            $factory = $this->factories[$type]();
            // Skip to next question when there is no proper handling of type
            if ($factory === null) {
                continue;
            }

            $this->output[] = $this->handleFactory($factory, $survey, $question);
        }

        return $this->output;
    }

    private function handleFactory($factory, $survey, $question)
    {
        $factory->setQuestion($question);
        $answers = $survey['answers'][$question['qid']] ?? [];
        if (!empty($answers)) {
            $factory->setAnswers($answers);
        }
        try {
            $output = $factory->process($question);
        } catch (Exception $e) {
            throw new InvalidArgumentException('There was an error processing question: ' . $question['type'] . ' ' . $question['qid']);
        }

        return $output;
    }

    private function fetchSurveyMetadata()
    {
        $sqlQuery = "
            SELECT 
                q.qid, q.sid, q.gid, q.type, q.title, q.parent_qid, q.scale_id, q.question_order, q.other,
                ql.question as question_text,
                a.aid, a.qid as answer_qid, a.code, a.sortorder, a.scale_id as answer_scale_id,
                al.answer,
                qa.attribute, qa.value
            FROM {{questions}} q
            LEFT JOIN {{question_l10ns}} ql ON q.qid = ql.qid AND ql.language = :language
            LEFT JOIN {{answers}} a ON q.qid = a.qid
            LEFT JOIN {{answer_l10ns}} al ON a.aid = al.aid AND al.language = :language
            LEFT JOIN {{question_attributes}} qa ON q.qid = qa.qid
            WHERE q.sid = :sid
                AND (q.parent_qid = 0 OR q.parent_qid IN (SELECT qid FROM {{questions}} WHERE sid = :sid))
            ORDER BY q.parent_qid, q.scale_id, q.question_order, q.title, a.sortorder, a.code
        ";
        $command = Yii::app()->db->createCommand($sqlQuery);
        $command->bindParam(':sid', $this->surveyId, PDO::PARAM_INT);
        $command->bindParam(':language', $this->language, PDO::PARAM_STR);
        $rows = $command->queryAll();

        $questions = [];
        $answers = [];

        foreach ($rows as $row) {
            $qid = $row['qid'];
            if ($row['parent_qid'] == 0) {
                if (empty($questions[$qid])) {
                    $questions[$qid] = [
                        'qid' => $qid, 'sid' => $row['sid'], 'gid' => $row['gid'],
                        'type' => $row['type'], 'title' => $row['title'],
                        'question' => flattenText($row['question_text']), 'other' => $row['other'],
                        'subQuestions' => [], 'attributes' => [],
                    ];
                }
            } else {
                if (empty($questions[$qid]['subQuestions'][$qid])) {
                    $questions[$row['parent_qid']]['subQuestions'][$qid] = [
                        'qid' => $qid, 'gid' => $row['gid'],
                        'title' => $row['title'], 'question' => flattenText($row['question_text']),
                        'scale_id' => $row['scale_id'] ?? 0, 'question_order' => $row['question_order']
                    ];
                }
            }
            if ($row['aid']) {
                $answers[$row['answer_qid']][$row['code']] = [
                    'aid' => $row['aid'], 'code' => $row['code'], 'answer' => flattenText($row['answer']),
                    'sortorder' => $row['sortorder'], 'scale_id' => $row['answer_scale_id']
                ];
            }
            if ($row['attribute'] && empty($questions[$qid]['attributes'][$row['attribute']])) {
                $questions[$qid]['attributes'][$row['attribute']] = $row['value'];
            }
        }

        return compact('questions', 'answers');
    }
}
