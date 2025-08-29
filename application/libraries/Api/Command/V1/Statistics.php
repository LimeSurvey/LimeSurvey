<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Models\Services\SurveyStatistics;
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

class Statistics implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected Permission $permission;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;
    private SurveyStatistics $surveyStatistics;

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
        ResponseFactory $responseFactory,
        SurveyStatistics $surveyStatistics
    ) {
        $this->permission = $permission;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
        $this->surveyStatistics = $surveyStatistics;
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
            $data = $this->surveyStatistics->buildOutputList($value, 'en', $surveyId, null);
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
}
