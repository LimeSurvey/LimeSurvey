<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use PDO;
use Permission;
use Question;
use QuestionAttribute;
use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use DI\FactoryInterface;
use Yii;

class Statistics implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected Permission $permission;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        TransformerOutputSurvey $transformerOutputSurvey,
        Permission $permission,
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    ) {
        $this->permission = $permission;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey list command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');
        if (!$this->permission->hasSurveyPermission($surveyId, 'statistics')) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $summary = ['datestampE', 'datestampG', 'datestampL', 'idG', 'idL'];

// Fetch all questions for the survey
        $questions = Question::model()->findAll([
            'condition' => 'sid = :sid',
            'params' => [':sid' => $surveyId],
            'index' => 'qid' // Index by qid for easy lookup
        ]);

// Fetch questions with type QT_COLON_ARRAY_NUMBERS
        $arrayNumberQuestions = Question::model()->findAll([
            'condition' => 'sid = :sid AND type = :type',
            'params' => [':sid' => $surveyId, ':type' => Question::QT_COLON_ARRAY_NUMBERS],
            'index' => 'qid'
        ]);

// Fetch all question attributes for relevant questions in one query
        $questionIds = array_keys($questions);
        $questionAttributes = QuestionAttribute::model()->findAll([
            'condition' => 'qid IN (' . implode(',', array_fill(0, count($questionIds), '?')) . ')',
            'params' => $questionIds,
            'index' => 'qid'
        ]);

// Fetch all subquestions for all relevant questions
        $subQuestions = Question::model()->findAll([
            'condition' => 'parent_qid IN (' . implode(',', array_fill(0, count($questionIds), '?')) . ')',
            'params' => $questionIds,
            'order' => 'parent_qid, scale_id, question_order, title'
        ]);

// Organize subquestions by parent_qid and scale_id
        $subQuestionsByParent = [];
        foreach ($subQuestions as $subQuestion) {
            $parentQid = $subQuestion->parent_qid;
            $scaleId = $subQuestion->scale_id ?? 0;
            $subQuestionsByParent[$parentQid][$scaleId][] = $subQuestion;
        }

// Process each question
        foreach ($questions as $row) {
            $type = $row->type;
            $qid = $row->qid;
            $gid = $row->gid;

            switch ($type) {
                case Question::QT_COLON_ARRAY_NUMBERS:
                    $attributes = $questionAttributes[$qid] ?? null;
                    if (!$attributes || !$attributes['input_boxes']) {
                        $scale0 = $subQuestionsByParent[$qid][0] ?? [];
                        $scale1 = $subQuestionsByParent[$qid][1] ?? [];
                        foreach ($scale0 as $row1) {
                            foreach ($scale1 as $row2) {
                                $summary[] = "{$surveyId}X{$gid}X{$qid}{$row1->title}_{$row2->title}";
                            }
                        }
                    }
                    break;

                case Question::QT_1_ARRAY_DUAL:
                    $subQuestionsForQid = $subQuestionsByParent[$qid][0] ?? [];
                    foreach ($subQuestionsForQid as $row1) {
                        $summary[] = "{$surveyId}X{$gid}X{$qid}{$row1->title}#0";
                        $summary[] = "{$surveyId}X{$gid}X{$qid}{$row1->title}#1";
                    }
                    break;

                case Question::QT_R_RANKING:
                    $subQuestionsForQid = $subQuestionsByParent[$qid][0] ?? [];
                    $count = count($subQuestionsForQid);
                    for ($i = 1; $i <= $count; $i++) {
                        $summary[] = "{$type}{$surveyId}X{$gid}X{$qid}-{$i}";
                    }
                    break;

                case Question::QT_A_ARRAY_5_POINT:
                case Question::QT_F_ARRAY:
                case Question::QT_H_ARRAY_COLUMN:
                case Question::QT_E_ARRAY_INC_SAME_DEC:
                case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                    $subQuestionsForQid = $subQuestionsByParent[$qid][0] ?? [];
                    foreach ($subQuestionsForQid as $row1) {
                        $summary[] = "{$surveyId}X{$gid}X{$qid}{$row1->title}";
                    }
                    break;

                case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                case Question::QT_M_MULTIPLE_CHOICE:
                case Question::QT_S_SHORT_FREE_TEXT:
                case Question::QT_T_LONG_FREE_TEXT:
                case Question::QT_N_NUMERICAL:
                    $summary[] = "{$type}{$surveyId}X{$gid}X{$qid}";
                    break;

                case Question::QT_K_MULTIPLE_NUMERICAL:
                case Question::QT_ASTERISK_EQUATION:
                case Question::QT_D_DATE:
                case Question::QT_VERTICAL_FILE_UPLOAD:
                case Question::QT_U_HUGE_FREE_TEXT:
                case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                case Question::QT_SEMICOLON_ARRAY_TEXT:
                case Question::QT_X_TEXT_DISPLAY:
                    // Skip these question types
                    break;

                default:
                    $summary[] = "{$surveyId}X{$gid}X{$qid}";
                    break;
            }
        }

        $output = [];
        foreach ($summary as $value) {
            $data = $this->buildOutputList($value, 'en', $surveyId, null, returnGlobal('statlang'));
            if (!empty($data)) {
                $output[] = $data;
            }
        }

        return $this->responseFactory
            ->makeSuccess([
                'total' => count($output),
                'statistics' => $output,
            ]);
    }

    protected function buildOutputList($rt, $language, $surveyid, $sql, $oLanguage)
    {
        $language = sanitize_languagecode($language);
        $surveyid = (int) $surveyid;

        // Initialize variables
        $survey = Survey::model()->findByPk($surveyid);
        $fieldmap = createFieldMap($survey, 'full', false, false, $language);
        $sQuestionType = substr($rt, 0, 1);

        // Fetch metadata
        $data = $this->fetchSurveyMetadata($surveyid, $language);

        // Process question type
        $questionData = $this->processQuestionType($rt, $sQuestionType, $survey, $fieldmap, $data, $sql);

        // Wrap in statistics array
        return $questionData ?? [];
    }

    /**
     * Fetches questions, subquestions, answers, and attributes in one query.
     *
     * @param int $surveyid Survey ID
     * @param string $language Language code
     * @return array Structured metadata
     */
    private function fetchSurveyMetadata($surveyid, $language)
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
        $command->bindParam(':sid', $surveyid, PDO::PARAM_INT);
        $command->bindParam(':language', $language, PDO::PARAM_STR);
        $rows = $command->queryAll();

        $questions = [];
        $subQuestions = [];
        $answers = [];
        $attributes = [];

        foreach ($rows as $row) {
            $qid = $row['qid'];
            if ($row['parent_qid'] == 0) {
                $questions[$qid] = (object)[
                    'qid' => $qid,
                    'sid' => $row['sid'],
                    'gid' => $row['gid'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                    'question' => flattenText($row['question_text']),
                    'other' => $row['other']
                ];
            } else {
                $subQuestions[$row['parent_qid']][$row['title']] = (object)[
                    'qid' => $qid,
                    'title' => $row['title'],
                    'question' => flattenText($row['question_text']),
                    'scale_id' => $row['scale_id'] ?? 0,
                    'question_order' => $row['question_order']
                ];
            }
            if ($row['aid']) {
                $answers[$row['answer_qid']][$row['code']] = (object)[
                    'aid' => $row['aid'],
                    'code' => $row['code'],
                    'answer' => flattenText($row['answer']),
                    'sortorder' => $row['sortorder'],
                    'scale_id' => $row['answer_scale_id']
                ];
            }
            if ($row['attribute']) {
                $attributes[$qid][$row['attribute']] = $row['value'];
            }
        }

        return compact('questions', 'subQuestions', 'answers', 'attributes');
    }

    /**
     * Processes the question type and returns question data.
     *
     * @param string $rt Field name
     * @param string $sQuestionType Question type
     * @param Survey $survey Survey model
     * @param array $fieldmap Field map
     * @param array $data Metadata
     * @param string $sql Additional SQL filter
     * @return array|null Question data or null if skipped
     */
    private function processQuestionType($rt, $sQuestionType, $survey, $fieldmap, $data, $sql)
    {
        switch ($sQuestionType) {
            case 'M':
            case 'P':
                return $this->processMultipleChoice($rt, $data, $survey->sid);
            case 'T':
            case 'S':
                return $this->processText($rt, $fieldmap, $data);
            case 'Q':
                return $this->processMultipleShortText($rt, $fieldmap, $data);
            case 'R':
                return $this->processRanking($rt, $data);
            case '|':
                return $this->processFileUpload($rt, $survey->sid, $data);
            case 'N':
            case 'K':
            case ':':
                return $this->processNumerical($rt, $fieldmap, $data, $sql);
            default:
                if (substr($rt, 0, 2) != 'id' && substr($rt, 0, 9) != 'datestamp' && $sQuestionType != 'D') {
                    return $this->processSingleOption($rt, $fieldmap, $data, $survey);
                }
                return null;
        }
    }

    /**
     * Processes multiple choice questions (M, P).
     *
     * @param string $rt Field name
     * @param array $data Metadata
     * @param int $surveyid Survey ID
     * @return array
     */
    private function processMultipleChoice($rt, $data, $surveyid)
    {
        [$qsid, $qgid, $qqid] = explode('X', substr($rt, 1), 3);
        $qqid = (int) $qqid;
        $question = $data['questions'][$qqid] ?? null;
        if (!$question) {
            return null;
        }

        $legend = [];
        $dataItems = [];
        foreach ($data['subQuestions'][$qqid] ?? [] as $subQuestion) {
            $mfield = substr($rt, 1) . $subQuestion->title;
            $legend[] = $subQuestion->title;
            $count = $this->getResponseCount($mfield, $surveyid);
            $dataItems[] = [
                'key' => $subQuestion->title,
                'value' => $count,
                'extraProp' => $subQuestion->question
            ];
        }
        if ($question->other == 'Y') {
            $mfield = substr($rt, 1) . 'other';
            $legend[] = 'other';
            $count = $this->getResponseCount($mfield, $surveyid);
            $dataItems[] = ['key' => 'other', 'value' => $count, 'extraProp' => 'Other'];
        }

        return [
            'qid' => $qqid,
            'parent_qid' => 0,
            'title' => $question->title,
            'legend' => $legend,
            'total' => array_sum(array_column($dataItems, 'value')),
            'data' => $dataItems
        ];
    }

    /**
     * Processes short/long text questions (T, S).
     *
     * @param string $rt Field name
     * @param array $fieldmap Field map
     * @param array $data Metadata
     * @return array|null
     */
    private function processText($rt, $fieldmap, $data)
    {
        $fld = substr($rt, 1);
        $fielddata = $fieldmap[$fld] ?? null;
        if (!$fielddata) {
            return null;
        }

        $question = $data['questions'][$fielddata['qid']] ?? null;
        if (!$question) {
            return null;
        }

        $legend = ['Answer', 'NoAnswer'];
        $count = $this->getResponseCount($fld, $fielddata['sid']);
        $dataItems = [
            ['key' => 'Answer', 'value' => $count, 'extraProp' => 'Answer'],
            ['key' => 'NoAnswer', 'value' => 0, 'extraProp' => 'No answer'] // No answer count not computed
        ];

        return [
            'qid' => $fielddata['qid'],
            'parent_qid' => $question->type == Question::QT_SEMICOLON_ARRAY_TEXT ? $fielddata['qid'] : 0,
            'title' => $question->title,
            'legend' => $legend,
            'total' => $count,
            'data' => $dataItems
        ];
    }

    /**
     * Processes multiple short text questions (Q).
     *
     * @param string $rt Field name
     * @param array $fieldmap Field map
     * @param array $data Metadata
     * @return array|null
     */
    private function processMultipleShortText($rt, $fieldmap, $data)
    {
        $fielddata = $fieldmap[substr($rt, 1)] ?? null;
        if (!$fielddata) {
            return null;
        }

        $qqid = $fielddata['qid'];
        $qaid = $fielddata['aid'];
        $question = $data['questions'][$qqid] ?? null;
        $subQuestion = $data['subQuestions'][$qqid][$qaid] ?? null;
        if (!$question || !$subQuestion) {
            return null;
        }

        $mfield = substr($rt, 1);
        $legend = ['Answer', 'NoAnswer'];
        $count = $this->getResponseCount($mfield, $fielddata['sid']);
        $dataItems = [
            ['key' => 'Answer', 'value' => $count, 'extraProp' => $subQuestion->question],
            ['key' => 'NoAnswer', 'value' => 0, 'extraProp' => 'No answer']
        ];

        return [
            'qid' => $qqid,
            'parent_qid' => $qqid,
            'title' => $question->title . " [{$subQuestion->question}]",
            'legend' => $legend,
            'total' => $count,
            'data' => $dataItems
        ];
    }

    /**
     * Processes ranking questions (R).
     *
     * @param string $rt Field name
     * @param array $data Metadata
     * @return array|null
     */
    private function processRanking($rt, $data)
    {
        $lengthofnumeral = substr($rt, -1);
        [$qsid, $qgid, $qqid] = explode('X', substr($rt, 1, strpos($rt, '-') - ($lengthofnumeral + 1)), 3);
        $qqid = (int) $qqid;
        $question = $data['questions'][$qqid] ?? null;
        if (!$question) {
            return null;
        }

        $mfield = substr($rt, 1, strpos($rt, '-') - 1);
        $legend = [];
        $dataItems = [];
        foreach ($data['answers'][$qqid] ?? [] as $answer) {
            if ($answer->scale_id == 0) {
                $legend[] = $answer->code;
                $count = $this->getResponseCount($mfield, $question->sid, $answer->code);
                $dataItems[] = [
                    'key' => $answer->code,
                    'value' => $count,
                    'extraProp' => $answer->answer
                ];
            }
        }

        return [
            'qid' => $qqid,
            'parent_qid' => 0,
            'title' => flattenText($question->title) . " [{$lengthofnumeral}]",
            'legend' => $legend,
            'total' => array_sum(array_column($dataItems, 'value')),
            'data' => $dataItems
        ];
    }

    /**
     * Processes file upload questions (|).
     *
     * @param string $rt Field name
     * @param int $surveyid Survey ID
     * @param array $data Metadata
     * @return array|null
     */
    private function processFileUpload($rt, $surveyid, $data)
    {
        [$qsid, $qgid, $qqid] = explode('X', substr($rt, 1), 3);
        $qqid = (int) $qqid;
        $question = $data['questions'][$qqid] ?? null;
        if (!$question) {
            return null;
        }

        $fieldname = substr($rt, 1);
        $query = "SELECT SUM(" . Yii::app()->db->quoteColumnName($fieldname . '_filecount') . ") as sum, AVG(" . Yii::app()->db->quoteColumnName($fieldname . '_filecount') . ") as avg, " . Yii::app()->db->quoteColumnName($fieldname) . " as json FROM {{survey_$surveyid}}";
        $rows = Yii::app()->db->createCommand($query)->queryAll();
        $statistics = [];
        $filecount = 0;
        $size = 0;
        $responsecount = count($rows);
        foreach ($rows as $row) {
            $statistics['total_files'] = (int) ($row['sum'] ?? 0);
            $statistics['avg_files_per_respondent'] = round((float) ($row['avg'] ?? 0), 2);
            $json = $row['json'] ?? '[]';
            $phparray = json_decode($json, true) ?: [];
            foreach ($phparray as $metadata) {
                $size += (int) ($metadata['size'] ?? 0);
                $filecount++;
            }
        }
        if ($filecount > 0) {
            $statistics['total_size_kb'] = $size;
            $statistics['avg_file_size_kb'] = round($size / $filecount, 2);
            $statistics['avg_size_per_respondent_kb'] = round($size / $responsecount, 2);
        }

        $legend = array_keys($statistics);
        $dataItems = array_map(fn($key, $value) => [
            'key' => $key,
            'value' => $value,
            'extraProp' => null
        ], array_keys($statistics), array_values($statistics));

        return [
            'qid' => $qqid,
            'parent_qid' => 0,
            'title' => $question->title,
            'legend' => $legend,
            'total' => $statistics['total_files'] ?? 0,
            'data' => $dataItems
        ];
    }

    /**
     * Processes numerical questions (N, K, :).
     *
     * @param string $rt Field name
     * @param array $fieldmap Field map
     * @param array $data Metadata
     * @param string $sql Additional SQL filter
     * @return array|null
     */
    private function processNumerical($rt, $fieldmap, $data, $sql)
    {
        $excludezeros = 1;
        if (!in_array(substr($rt, -1), ['G', 'L', '='])) {
            $fld = substr($rt, 1);
            $fielddata = $fieldmap[$fld] ?? null;
            if (!$fielddata) {
                return null;
            }

            $question = $data['questions'][$fielddata['qid']] ?? null;
            if (!$question) {
                return null;
            }

            $title = flattenText($fielddata['title']);
            if ($fielddata['type'] == 'K') {
                $title .= " [{$fielddata['subquestion']}]";
            } elseif ($fielddata['type'] == ':') {
                [$myans, $mylabel] = explode('_', (string) $fielddata['aid']);
                $title .= "[$myans][$mylabel]";
            }

            $statistics = $this->calculateNumericalStatistics($rt, $fielddata, $sql, $excludezeros);
            $legend = array_keys($statistics);
            $dataItems = array_map(fn($key, $value) => [
                'key' => $key,
                'value' => $value,
                'extraProp' => null
            ], array_keys($statistics), array_values($statistics));

            return [
                'qid' => $fielddata['qid'],
                'parent_qid' => $fielddata['type'] == 'K' || $fielddata['type'] == ':' ? $fielddata['qid'] : 0,
                'title' => $title,
                'legend' => $legend,
                'total' => $statistics['count'] ?? 0,
                'data' => $dataItems
            ];
        }
        return null;
    }

    /**
     * Calculates statistics for numerical questions.
     *
     * @param string $rt Field name
     * @param array $fielddata Field map data
     * @param string $sql Additional SQL filter
     * @param bool $excludezeros Exclude zero values
     * @return array Statistics
     */
    private function calculateNumericalStatistics($rt, $fielddata, $sql, $excludezeros)
    {
        $fieldname = substr($rt, 1);
        $query = "SELECT " . Yii::app()->db->quoteColumnName($fieldname) . " FROM {{survey_{$fielddata['sid']}}} WHERE " . Yii::app()->db->quoteColumnName($fieldname) . " IS NOT NULL";
        if ($fielddata['type'] == ':') {
            $query .= " AND " . Yii::app()->db->quoteColumnName($fieldname) . " <> ''";
        }
        if (!$excludezeros) {
            $query .= " AND (" . Yii::app()->db->quoteColumnName($fieldname) . " != 0)";
        }
        if (incompleteAnsFilterState() === 'incomplete') {
            $query .= " AND submitdate IS NULL";
        } elseif (incompleteAnsFilterState() === 'complete') {
            $query .= " AND submitdate IS NOT NULL";
        }
        if (!empty($sql)) {
            $query .= " AND $sql";
        }
        $rows = Yii::app()->db->createCommand($query)->queryAll();
        $values = array_map(fn($row) => $fielddata['encrypted'] === 'Y' ? LSActiveRecord::decryptSingle($row[$fieldname]) : $row[$fieldname], $rows);
        $statistics = [];
        if (!empty($values)) {
            $statistics['sum'] = array_sum($values);
            $statistics['average'] = round($statistics['sum'] / count($values), 2);
            $statistics['standard_deviation'] = round(standardDeviation($values), 2);
            $statistics['minimum'] = min($values);
            $statistics['maximum'] = max($values);
        }
        $statistics['count'] = $this->getQuartile(0, $fielddata, $sql, $excludezeros);
        $quartiles = [
            $this->getQuartile(1, $fielddata, $sql, $excludezeros),
            $this->getQuartile(2, $fielddata, $sql, $excludezeros),
            $this->getQuartile(3, $fielddata, $sql, $excludezeros)
        ];
        if (isset($quartiles[0])) $statistics['q1'] = $quartiles[0];
        if (isset($quartiles[1])) $statistics['median'] = $quartiles[1];
        if (isset($quartiles[2])) $statistics['q3'] = $quartiles[2];
        return $statistics;
    }

    /**
     * Processes single option questions (A, B, C, E, F, H, G, Y, I, 5, 1, etc.).
     *
     * @param string $rt Field name
     * @param array $fieldmap Field map
     * @param array $data Metadata
     * @param Survey $survey Survey model
     * @return array|null
     */
    private function processSingleOption($rt, $fieldmap, $data, $survey)
    {
        $fielddata = $fieldmap[$rt] ?? null;
        if (!$fielddata) {
            return null;
        }

        $qqid = $fielddata['qid'];
        $qanswer = $fielddata['aid'] ?? '';
        $question = $data['questions'][$qqid] ?? null;
        if (!$question) {
            return null;
        }

        $legend = [];
        $dataItems = [];
        $title = flattenText($question->title);
        $qother = $question->other;

        switch ($fielddata['type']) {
            case Question::QT_A_ARRAY_5_POINT:
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                $subQuestion = $data['subQuestions'][$qqid][$qanswer] ?? null;
                if ($subQuestion) {
                    $title .= "($qanswer)[{$subQuestion->question}]";
                    $max = $fielddata['type'] == Question::QT_A_ARRAY_5_POINT ? 5 : 10;
                    for ($i = 1; $i <= $max; $i++) {
                        $legend[] = "$i";
                        $count = $this->getResponseCount($rt, $fielddata['sid'], "$i");
                        $dataItems[] = ['key' => "$i", 'value' => $count, 'extraProp' => "$i"];
                    }
                }
                break;
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                $subQuestion = $data['subQuestions'][$qqid][$qanswer] ?? null;
                if ($subQuestion) {
                    $title .= "($qanswer)[{$subQuestion->question}]";
                    $options = [
                        ['code' => 'Y', 'text' => 'Yes'],
                        ['code' => 'N', 'text' => 'No'],
                        ['code' => 'U', 'text' => 'Uncertain']
                    ];
                    foreach ($options as $option) {
                        $legend[] = $option['code'];
                        $count = $this->getResponseCount($rt, $fielddata['sid'], $option['code']);
                        $dataItems[] = ['key' => $option['code'], 'value' => $count, 'extraProp' => $option['text']];
                    }
                }
                break;
            case Question::QT_E_ARRAY_INC_SAME_DEC:
                $subQuestion = $data['subQuestions'][$qqid][$qanswer] ?? null;
                if ($subQuestion) {
                    $title .= "($qanswer)[{$subQuestion->question}]";
                    $options = [
                        ['code' => 'I', 'text' => 'Increase'],
                        ['code' => 'S', 'text' => 'Same'],
                        ['code' => 'D', 'text' => 'Decrease']
                    ];
                    foreach ($options as $option) {
                        $legend[] = $option['code'];
                        $count = $this->getResponseCount($rt, $fielddata['sid'], $option['code']);
                        $dataItems[] = ['key' => $option['code'], 'value' => $count, 'extraProp' => $option['text']];
                    }
                }
                break;
            case Question::QT_SEMICOLON_ARRAY_TEXT:
                [$qacode, $licode] = explode('_', (string) $qanswer);
                $subQuestion = $data['subQuestions'][$qqid][$qacode] ?? null;
                if ($subQuestion && isset($data['answers'][$qqid][$licode])) {
                    $ltext = $data['answers'][$qqid][$licode]->answer;
                    $title .= "($qanswer)[{$subQuestion->question}][$ltext]";
                    $count = $this->getResponseCount($rt, $fielddata['sid']);
                    $legend[] = $licode;
                    $dataItems[] = ['key' => $licode, 'value' => $count, 'extraProp' => $ltext];
                }
                break;
            case Question::QT_COLON_ARRAY_NUMBERS:
                $statistics = $this->processArrayNumbers($fielddata, $data['attributes'][$qqid] ?? [], $qanswer);
                $legend = array_keys($statistics);
                $dataItems = array_map(fn($key, $value) => [
                    'key' => $key,
                    'value' => $value,
                    'extraProp' => null
                ], array_keys($statistics), array_values($statistics));
                $title .= "[$qanswer]";
                break;
            case Question::QT_F_ARRAY:
            case Question::QT_H_ARRAY_COLUMN:
                $subQuestion = $data['subQuestions'][$qqid][$qanswer] ?? null;
                if ($subQuestion) {
                    $title .= "($qanswer)[{$subQuestion->question}]";
                    foreach ($data['answers'][$qqid] ?? [] as $answer) {
                        if ($answer->scale_id == 0) {
                            $legend[] = $answer->code;
                            $count = $this->getResponseCount($rt, $fielddata['sid'], $answer->code);
                            $dataItems[] = ['key' => $answer->code, 'value' => $count, 'extraProp' => $answer->answer];
                        }
                    }
                }
                break;
            case Question::QT_G_GENDER:
                $options = [
                    ['code' => 'F', 'text' => 'Female'],
                    ['code' => 'M', 'text' => 'Male']
                ];
                foreach ($options as $option) {
                    $legend[] = $option['code'];
                    $count = $this->getResponseCount($rt, $fielddata['sid'], $option['code']);
                    $dataItems[] = ['key' => $option['code'], 'value' => $count, 'extraProp' => $option['text']];
                }
                break;
            case Question::QT_Y_YES_NO_RADIO:
                $options = [
                    ['code' => 'Y', 'text' => 'Yes'],
                    ['code' => 'N', 'text' => 'No']
                ];
                foreach ($options as $option) {
                    $legend[] = $option['code'];
                    $count = $this->getResponseCount($rt, $fielddata['sid'], $option['code']);
                    $dataItems[] = ['key' => $option['code'], 'value' => $count, 'extraProp' => $option['text']];
                }
                break;
            case Question::QT_I_LANGUAGE:
                $options = array_map(fn($lang) => ['code' => $lang, 'text' => getLanguageNameFromCode($lang, false)], $survey->getAllLanguages());
                foreach ($options as $option) {
                    $legend[] = $option['code'];
                    $count = $this->getResponseCount($rt, $fielddata['sid'], $option['code']);
                    $dataItems[] = ['key' => $option['code'], 'value' => $count, 'extraProp' => $option['text']];
                }
                break;
            case Question::QT_5_POINT_CHOICE:
                for ($i = 1; $i <= 5; $i++) {
                    $legend[] = "$i";
                    $count = $this->getResponseCount($rt, $fielddata['sid'], "$i");
                    $dataItems[] = ['key' => "$i", 'value' => $count, 'extraProp' => "$i"];
                }
                break;
            case Question::QT_1_ARRAY_DUAL:
                return $this->processDualScale($rt, $fielddata, $data, $qqid, $qanswer);
        }

        if (in_array($fielddata['type'], [Question::QT_L_LIST, Question::QT_EXCLAMATION_LIST_DROPDOWN]) && $qother == Question::QT_Y_YES_NO_RADIO) {
            $mfield = $fielddata['fieldname'] . 'other';
            $legend[] = 'other';
            $count = $this->getResponseCount($mfield, $fielddata['sid']);
            $dataItems[] = ['key' => 'other', 'value' => $count, 'extraProp' => 'Other'];
        }
        if ($fielddata['type'] == Question::QT_O_LIST_WITH_COMMENT) {
            $mfield = $fielddata['fieldname'] . 'comment';
            $legend[] = 'comment';
            $count = $this->getResponseCount($mfield, $fielddata['sid']);
            $dataItems[] = ['key' => 'comment', 'value' => $count, 'extraProp' => 'Comments'];
        }

        $legend[] = 'NoAnswer';
        $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'extraProp' => 'No answer'];

        return [
            'qid' => $qqid,
            'parent_qid' => 0,
            'title' => $title,
            'legend' => $legend,
            'total' => array_sum(array_column($dataItems, 'value')),
            'data' => $dataItems
        ];
    }

    /**
     * Processes array numbers questions (:).
     *
     * @param array $fielddata Field map data
     * @param array $attributes Question attributes
     * @param string $qanswer Answer code
     * @return array Statistics
     */
    private function processArrayNumbers($fielddata, $attributes, $qanswer)
    {
        $minvalue = 1;
        $maxvalue = 10;
        if (!empty($attributes['multiflexible_max']) && empty($attributes['multiflexible_min'])) {
            $maxvalue = $attributes['multiflexible_max'];
        } elseif (!empty($attributes['multiflexible_min']) && empty($attributes['multiflexible_max'])) {
            $minvalue = $attributes['multiflexible_min'];
            $maxvalue = $minvalue + 10;
        } elseif (!empty($attributes['multiflexible_min']) && !empty($attributes['multiflexible_max']) && $attributes['multiflexible_min'] < $attributes['multiflexible_max']) {
            $minvalue = $attributes['multiflexible_min'];
            $maxvalue = $attributes['multiflexible_max'];
        }
        $stepvalue = !empty($attributes['multiflexible_step']) && $attributes['multiflexible_step'] > 0 ? $attributes['multiflexible_step'] : 1;
        if (($attributes['reverse'] ?? 0) == 1) {
            [$minvalue, $maxvalue] = [$maxvalue, $minvalue];
            $stepvalue = -$stepvalue;
        }
        if (($attributes['multiflexible_checkbox'] ?? 0) != 0) {
            $minvalue = 0;
            $maxvalue = 1;
            $stepvalue = 1;
        }
        $statistics = [];
        for ($i = $minvalue; $i <= $maxvalue; $i += $stepvalue) {
            $count = $this->getResponseCount($fielddata['fieldname'], $fielddata['sid'], "$i");
            $statistics["value_$i"] = $count;
        }
        return $statistics;
    }

    /**
     * Processes dual scale array questions (1).
     *
     * @param string $rt Field name
     * @param array $fielddata Field map data
     * @param array $data Metadata
     * @param int $qqid Question ID
     * @param string $qanswer Answer code
     * @return array|null
     */
    private function processDualScale($rt, $fielddata, $data, $qqid, $qanswer)
    {
        $subQuestion = $data['subQuestions'][$qqid][$qanswer] ?? null;
        if (!$subQuestion) {
            return null;
        }

        $labelno = substr($rt, -1) == 0 ? 'Label 1' : 'Label 2';
        $title = flattenText($data['questions'][$qqid]->title) . " [{$subQuestion->question}][$labelno]";
        $legend = [];
        $dataItems = [];
        foreach ($data['answers'][$qqid] ?? [] as $answer) {
            if ($answer->scale_id == (substr($rt, -1) == 0 ? 0 : 1)) {
                $legend[] = $answer->code;
                $count = $this->getResponseCount($rt, $fielddata['sid'], $answer->code);
                $dataItems[] = ['key' => $answer->code, 'value' => $count, 'extraProp' => $answer->answer];
            }
        }

        $legend[] = 'NoAnswer';
        $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'extraProp' => 'No answer'];

        return [
            'qid' => $qqid,
            'parent_qid' => $qqid,
            'title' => $title,
            'legend' => $legend,
            'total' => array_sum(array_column($dataItems, 'value')),
            'data' => $dataItems
        ];
    }

    /**
     * Gets the response count for a field.
     *
     * @param string $fieldname Field name
     * @param int $surveyid Survey ID
     * @param string|null $value Specific value to count (optional)
     * @return int Response count
     */
    private function getResponseCount($fieldname, $surveyid, $value = null)
    {
        $query = "SELECT COUNT(*) as cnt FROM {{survey_$surveyid}} WHERE " . Yii::app()->db->quoteColumnName($fieldname) . " IS NOT NULL";
        if ($value !== null) {
            $query .= " AND " . Yii::app()->db->quoteColumnName($fieldname) . " = :value";
        }
        $command = Yii::app()->db->createCommand($query);
        if ($value !== null) {
            $command->bindParam(':value', $value, PDO::PARAM_STR);
        }
        return (int) $command->queryScalar();
    }
}
