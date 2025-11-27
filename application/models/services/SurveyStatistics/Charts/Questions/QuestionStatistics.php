<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions;

use CDbCommand;
use Exception;
use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\{MultipleChoiceProcessor,
    SingleOptionMultipleChartsProcessor,
    TextProcessor,
    RankingProcessor,
    SingleOptionProcessor,
    DualScaleProcessor
};
use Question;
use Yii;

class QuestionStatistics implements StatisticsChartInterface
{
    private array $factories;
    private int $surveyId;
    private string $language;

    private array $output = [];

    private $filters = null;

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

    public function setFilters(StatisticsResponseFilters $filters): void
    {
        $this->filters = $filters;
    }

    private function handleFactory($factory, $survey, $question)
    {
        $factory->setQuestion($question);
        $answers = $survey['answers'][$question['qid']] ?? [];
        if (!empty($answers)) {
            $factory->setAnswers($answers);
        }

        if (!empty($this->filters) && $this->filters->count() > 0) {
            foreach ($this->filters->getFilters() as $key => $value) {
                if ($value !== null) {
                    $method = 'set' . ucfirst($key);
                    if (method_exists($factory, $method)) {
                        $factory->$method($value);
                    }
                }
            }
        }

        try {
            $output = $factory->process($question);
        } catch (Exception $e) {
            throw new InvalidArgumentException('There was an error processing question: ' . $question['type'] . ' ' . $question['qid']);
        }

        return $output;
    }

    private function buildBaseQuery(): CDbCommand
    {
        $select = [
            'q.qid', 'q.sid', 'q.gid', 'q.type', 'q.title',
            'q.parent_qid', 'q.scale_id', 'q.question_order', 'q.other',
            'ql.question as question_text',
            'a.aid', 'a.qid as answer_qid', 'a.code', 'a.sortorder',
            'a.scale_id as answer_scale_id',
            'al.answer',
            'qa.attribute', 'qa.value'
        ];

        $command = Yii::app()->db->createCommand()
            ->select($select)
            ->from('{{questions}} q')
            ->leftJoin('{{question_l10ns}} ql', 'q.qid = ql.qid AND ql.language = :language')
            ->leftJoin('{{answers}} a', 'q.qid = a.qid')
            ->leftJoin('{{answer_l10ns}} al', 'a.aid = al.aid AND al.language = :language')
            ->leftJoin('{{question_attributes}} qa', 'q.qid = qa.qid')
            ->where('q.sid = :sid AND (q.parent_qid = 0 OR q.parent_qid IN (SELECT qid FROM {{questions}} WHERE sid = :sid))')
            ->order('q.parent_qid ASC, q.scale_id ASC, q.question_order ASC, q.title ASC, a.sortorder ASC, a.code ASC');

        $command->params = [
            ':sid' => $this->surveyId,
            ':language' => $this->language,
        ];

        return $command;
    }

    private function fetchSurveyMetadata()
    {
        $command = $this->buildBaseQuery();
        $rows = $command->queryAll();

        $questions = [];
        $answers = [];

        foreach ($rows as $row) {
            $qid = (int) $row['qid'];
            if ((int) $row['parent_qid'] === 0) {
                if (empty($questions[$qid])) {
                    $questions[$qid] = [
                        'qid' => $qid, 'sid' => $row['sid'], 'gid' => $row['gid'],
                        'type' => $row['type'], 'title' => $row['title'],
                        'question' => flattenText($row['question_text']), 'other' => $row['other'],
                        'subQuestions' => [], 'attributes' => [],
                    ];
                }
            } else {
                if (empty($questions[$row['parent_qid']]['subQuestions'][$qid])) {
                    $questions[$row['parent_qid']]['subQuestions'][$qid] = [
                        'qid' => $qid, 'gid' => $row['gid'],
                        'title' => $row['title'], 'question' => flattenText($row['question_text']),
                        'scale_id' => $row['scale_id'] ?? 0, 'question_order' => $row['question_order']
                    ];
                }
            }
            if (!empty($row['aid'])) {
                $answers[$row['answer_qid']][$row['code']] = [
                    'aid' => $row['aid'], 'code' => $row['code'], 'answer' => flattenText($row['answer']),
                    'sortorder' => $row['sortorder'], 'scale_id' => $row['answer_scale_id']
                ];
            }
            if (!empty($row['attribute']) && empty($questions[$qid]['attributes'][$row['attribute']])) {
                $questions[$qid]['attributes'][$row['attribute']] = $row['value'];
            }
        }

        return compact('questions', 'answers');
    }
}
