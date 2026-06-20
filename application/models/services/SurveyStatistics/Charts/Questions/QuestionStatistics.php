<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions;

use CDbCommand;
use Exception;
use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\{ArrayNumbersProcessor,
    ArrayTextProcessor,
    MultipleChoiceProcessor,
    ResponseAggregateBatch,
    SingleOptionMultipleChartsProcessor,
    TextProcessor,
    RankingProcessor,
    SingleOptionProcessor,
    DualScaleProcessor};
use Question;
use QuestionType;
use Yii;

class QuestionStatistics implements StatisticsChartInterface
{
    private array $factories;
    private int $surveyId;
    private string $language;

    private array $output = [];

    private $filters = null;

    /** @var int Zero-based page index, used when a page size is set */
    private int $page = 0;

    /** @var int|null Charts per page; null disables pagination */
    private ?int $pageSize = null;

    /** @var array|null Pagination details of the last run, null when unpaginated */
    private ?array $paginationMeta = null;

    /** @var array<string, string>|null Cached type code -> human-readable description map */
    private ?array $typeDescriptions = null;

    public function __construct()
    {
        $this->factories = [
            Question::QT_M_MULTIPLE_CHOICE => fn() => new MultipleChoiceProcessor(),
//            Question::QT_N_NUMERICAL => fn() => new MultipleChoiceProcessor(),
            Question::QT_COLON_ARRAY_NUMBERS => fn() => new ArrayNumbersProcessor(),
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
            Question::QT_SEMICOLON_ARRAY_TEXT => fn() => new ArrayTextProcessor(),
            Question::QT_X_TEXT_DISPLAY => fn() => null,

            // Single option with multiple graphs for each subquestion
            Question::QT_A_ARRAY_5_POINT => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_E_ARRAY_INC_SAME_DEC => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_B_ARRAY_10_CHOICE_QUESTIONS => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_C_ARRAY_YES_UNCERTAIN_NO => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_F_ARRAY => fn() => new SingleOptionMultipleChartsProcessor(),
            Question::QT_H_ARRAY_COLUMN => fn() => new SingleOptionMultipleChartsProcessor(),

            Question::QT_G_GENDER => fn() => new SingleOptionProcessor(),
            Question::QT_Y_YES_NO_RADIO => fn() => new SingleOptionProcessor(),
            Question::QT_I_LANGUAGE => fn() => new SingleOptionProcessor(),
            Question::QT_5_POINT_CHOICE => fn() => new SingleOptionProcessor(),
            Question::QT_L_LIST => fn() => new SingleOptionProcessor(),
            Question::QT_EXCLAMATION_LIST_DROPDOWN => fn() => new SingleOptionProcessor(),
            // List with comment: chart the list answers (plus a comment count);
            // the comment texts are shown via the comments view.
            Question::QT_O_LIST_WITH_COMMENT => fn() => new SingleOptionProcessor(),

            'default' => fn() => null,
        ];
    }

    public function run(int $surveyId, string $language = 'en'): array
    {
        $this->surveyId = $surveyId;
        $this->language = $language;
        $this->output = [];

        $survey = $this->fetchSurveyMetadata();
        $surveyQuestions = $survey['questions'];

        $batch = new ResponseAggregateBatch($surveyId, $this->filters);

        // Pair each chart-producing question with its processor; types without
        // a processor (equations, dates, text display, ...) produce no chart
        // and never occupy a page slot.
        $eligible = [];
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

            $eligible[] = [$factory, $question];
        }

        $eligible = $this->paginate($eligible);

        $jobs = [];
        foreach ($eligible as [$factory, $question]) {
            $jobs[] = $this->planFactory($factory, $batch, $survey, $question);
        }

        $batch->execute();

        foreach ($jobs as [$plans, $question]) {
            $this->output[] = $this->resolvePlans($plans, $question);
        }

        return $this->output;
    }

    public function setFilters(StatisticsResponseFilters $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * Limit the run to one page of chart-producing questions.
     *
     * @param int $page Zero-based page index
     * @param int $pageSize Charts per page
     */
    public function setPagination(int $page, int $pageSize): void
    {
        if ($page < 0 || $pageSize < 1) {
            throw new InvalidArgumentException('Invalid pagination parameters');
        }
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    /**
     * Pagination details of the last run, null when pagination was not set.
     */
    public function getPaginationMeta(): ?array
    {
        return $this->paginationMeta;
    }

    /**
     * Slice the eligible [factory, question] pairs to the configured page and
     * record the pagination meta.
     */
    private function paginate(array $eligible): array
    {
        $this->paginationMeta = null;
        if ($this->pageSize === null) {
            return $eligible;
        }

        $total = count($eligible);
        $offset = $this->page * $this->pageSize;
        $pageItems = array_slice($eligible, $offset, $this->pageSize);
        $this->paginationMeta = [
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'total' => $total,
            'hasMore' => $offset + count($pageItems) < $total,
        ];

        return $pageItems;
    }

    /**
     * Configure the processor and let it plan its charts against the batch.
     *
     * @return array{0: array, 1: array} [chart plan(s), question data]
     */
    private function planFactory($factory, ResponseAggregateBatch $batch, $survey, $question): array
    {
        $factory->setBatch($batch);
        $factory->setQuestion($question);
        $answers = $survey['answers'][$question['qid']] ?? [];
        if (!empty($answers)) {
            $factory->setAnswers($answers);
        }

        try {
            $plans = $factory->process();
        } catch (Exception $e) {
            throw new InvalidArgumentException('There was an error processing question: ' . $question['type'] . ' ' . $question['qid']);
        }

        return [$plans, $factory->getQuestion()];
    }

    /**
     * Resolve one plan or a list of plans into StatisticsChartDTO(s).
     *
     * @param array $plans Single chart plan (has 'title') or list of plans
     * @param array $question Question data for the chart meta
     * @return StatisticsChartDTO|StatisticsChartDTO[]
     */
    private function resolvePlans(array $plans, array $question)
    {
        if (isset($plans['title'])) {
            $dto = $this->resolvePlan($plans, $question);
            $this->trimMeta($dto);
            return $dto;
        }

        $dtos = array_map(fn($plan) => $this->resolvePlan($plan, $question), $plans);
        $this->trimMeta($dtos);
        return $dtos;
    }

    /**
     * Materialize a chart plan: resolve every deferred value against the
     * executed batch and compute the total.
     */
    private function resolvePlan(array $plan, array $question): StatisticsChartDTO
    {
        $data = [];
        $total = 0;
        foreach ($plan['data'] as $item) {
            if (isset($item['value']) && is_callable($item['value'])) {
                $item['value'] = (int)$item['value']();
            }
            // Resolve an optional deferred per-row breakdown (e.g. ranking's
            // per-position counts) the same way as the main value.
            if (!empty($item['ranks']) && is_array($item['ranks'])) {
                foreach ($item['ranks'] as $i => $rankRow) {
                    if (isset($rankRow['value']) && is_callable($rankRow['value'])) {
                        $item['ranks'][$i]['value'] = (int)$rankRow['value']();
                    }
                }
            }
            // Resolve a deferred stacked breakdown (array-type segments); when
            // the row has no own value, its total is the sum of its segments.
            if (!empty($item['segments']) && is_array($item['segments'])) {
                $segmentsTotal = 0;
                foreach ($item['segments'] as $i => $segment) {
                    if (isset($segment['value']) && is_callable($segment['value'])) {
                        $item['segments'][$i]['value'] = (int)$segment['value']();
                    }
                    $segmentsTotal += (int)$item['segments'][$i]['value'];
                }
                if (!isset($item['value']) || !is_int($item['value'])) {
                    $item['value'] = $segmentsTotal;
                }
            }
            $total += is_int($item['value'] ?? null) ? $item['value'] : 0;
            $data[] = $item;
        }

        return new StatisticsChartDTO(
            $plan['title'],
            $plan['legend'],
            $data,
            $total,
            ['question' => $question]
        );
    }

    /**
     * @param StatisticsChartDTO|StatisticsChartDTO[] $output
     */
    private function trimMeta($output): void
    {
        $dtos = is_array($output) ? $output : [$output];
        foreach ($dtos as $dto) {
            if (!$dto instanceof StatisticsChartDTO) {
                continue;
            }
            $meta = $dto->getMeta();
            $question = $meta['question'] ?? null;
            if (!is_array($question)) {
                continue;
            }
            $meta['question'] = [
                'qid' => $question['qid'] ?? null,
                'gid' => $question['gid'] ?? null,
                'code' => $question['title'] ?? null,
                'type' => $question['type'] ?? null,
                // Human-readable question type description (e.g. "List (Radio)").
                'typeLabel' => QuestionType::modelsAttributes($this->language)[$question['type']]['description'] ?? $question['type'] ?? null,
                // Theme code (e.g. "image_select-listradio") used to resolve the
                // specific theme display name and image handling on the client.
                'themeName' => $question['question_theme_name'] ?? null,
                'help' => $question['help'] ?? null,
                'attributes' => $question['attributes'] ?? [],
            ];
            $dto->setMeta($meta);
        }
    }

    private function buildBaseQuery(): CDbCommand
    {
        $select = [
            'q.qid', 'q.sid', 'q.gid', 'q.type', 'q.title',
            'q.parent_qid', 'q.scale_id', 'q.question_order', 'q.other',
            'q.question_theme_name',
            'ql.question as question_text', 'ql.help as help_text',
            'a.aid', 'a.qid as answer_qid', 'a.code', 'a.sortorder',
            'a.scale_id as answer_scale_id',
            'al.answer',
            'qa.attribute', 'qa.value'
        ];

        // Charts follow the survey structure: question groups in their survey
        // order, questions in their group order. Paginated pages therefore
        // load group by group.
        $command = Yii::app()->db->createCommand()
            ->select($select)
            ->from('{{questions}} q')
            ->leftJoin('{{groups}} g', 'q.gid = g.gid')
            ->leftJoin('{{question_l10ns}} ql', 'q.qid = ql.qid AND ql.language = :language')
            ->leftJoin('{{answers}} a', 'q.qid = a.qid')
            ->leftJoin('{{answer_l10ns}} al', 'a.aid = al.aid AND al.language = :language')
            ->leftJoin('{{question_attributes}} qa', 'q.qid = qa.qid')
            ->where('q.sid = :sid AND (q.parent_qid = 0 OR q.parent_qid IN (SELECT qid FROM {{questions}} WHERE sid = :sid))')
            ->order('q.parent_qid ASC, g.group_order ASC, q.scale_id ASC, q.question_order ASC, q.title ASC, a.sortorder ASC, a.code ASC');

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
                        'question' => flattenText($row['question_text']),
                        'help' => flattenText($row['help_text']), 'other' => $row['other'],
                        'question_theme_name' => $row['question_theme_name'],
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
