<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
use LimeSurvey\Models\Services\{
    QuestionAggregateService\QuestionService,
    QuestionAggregateService\L10nService,
    QuestionAggregateService\AttributesService,
    QuestionAggregateService\AnswersService,
    QuestionAggregateService\SubQuestionsService,
    Proxy\ProxyExpressionManager,
    Exception\PersistErrorException,
    Exception\NotFoundException,
    Exception\PermissionDeniedException
};

/**
 * Question Aggregate Save Service
 */
class SaveService
{
    private QuestionService $questionService;
    private L10nService $l10nService;
    private AttributesService $attributesService;
    private AnswersService $answersService;
    private SubQuestionsService $subQuestionsService;
    private ProxyExpressionManager $proxyExpressionManager;

    public function __construct(
        QuestionService $questionService,
        L10nService $l10nService,
        AttributesService $attributesService,
        AnswersService $answersService,
        SubQuestionsService $subQuestionsService,
        ProxyExpressionManager $proxyExpressionManager
    ) {
        $this->questionService = $questionService;
        $this->l10nService = $l10nService;
        $this->attributesService = $attributesService;
        $this->answersService = $answersService;
        $this->subQuestionsService = $subQuestionsService;
        $this->proxyExpressionManager = $proxyExpressionManager;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param int $surveyId
     * @param array {
     *  sid: int,
     *  ?question: array{
     *      ?qid: int,
     *      ?sid: int,
     *      ?gid: int,
     *      ?type: string,
     *      ?other: string,
     *      ?mandatory: string,
     *      ?relevance: int,
     *      ?group_name: string,
     *      ?modulename: string,
     *      ?encrypted: string,
     *      ?subqestions: array,
     *      ?save_as_default: string,
     *      ?clear_default: string,
     *      ...<array-key, mixed>
     *  },
     *  ?questionL10N: array{
     *      ...<array-key, array{
     *          question: string,
     *          help: string,
     *          ?language: string,
     *          ?script: string
     *      }>
     *  },
     *  ?subquestions: array{
     *      ...<array-key, mixed>
     *  },
     *  ?answeroptions: array{
     *      ...<array-key, mixed>
     *  },
     *  ?advancedSettings: array{
     *      ?logic: array{
     *          ?min_answers: int,
     *          ?max_answers: int,
     *          ?array_filter_style: int,
     *          ?array_filter: string,
     *          ?array_filter_exclude: string,
     *          ?exclude_all_others: int,
     *          ?random_group: string,
     *          ?em_validation_q: string,
     *          ?em_validation_q_tip: array{
     *              ?en: string,
     *              ?de: string,
     *              ...<array-key, mixed>
     *          },
     *          ...<array-key, mixed>
     *      },
     *      ?display: array{
     *          ...<array-key, mixed>
     *      },
     *      ?statistics: array{
     *          ...<array-key, mixed>
     *      },
     *      ...<array-key, mixed>
     *  }
     * } $input
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @return Question
     */
    public function save($surveyId, $input)
    {
        $data = $this->normaliseInput($surveyId, $input);

        $question = $this->questionService
            ->save($data);

        $this->l10nService->save(
            $question->qid,
            $data['questionL10n']
        );

        $this->attributesService
            ->saveAdvanced(
                $question,
                $data['advancedSettings']
            );

        $this->attributesService->saveMissingAttributes(
            $question,
            $surveyId
        );

        $this->attributesService
            ->save(
                $question,
                $data['question']
            );

        if (isset($data['answeroptions'])) {
            $this->answersService->save(
                $question,
                $data['answeroptions']
            );
        }

        if (isset($data['subquestions'])) {
            $this->subQuestionsService->save(
                $question,
                $data['subquestions']
            );
        }

        $this->proxyExpressionManager->setDirtyFlag();

        return $question;
    }

    /**
     * Normalise input
     *
     * @param array
     * @return array
     */
    public function normaliseInput($surveyId, $input)
    {
        $input  = $input ?? [];

        $data = [];
        $data['question']         = $input['question'] ?? [];
        $data['question']['sid']  = $surveyId;
        $data['question']['qid']  = $data['question']['qid'] ?? null;
        // / questionI10N  needs to be updatecd in the interface to questionL10n
        $data['questionL10n']     = $input['questionI10N'] ?? [];
        $data['advancedSettings'] = $input['advancedSettings'] ?? [];
        $data['answeroptions']    = $input['answeroptions'] ?? null;
        $data['subquestions']     = $input['subquestions'] ?? null;

        return $data;
    }
}
