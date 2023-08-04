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
 * Question Aggregate Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class SaveService
{
    private QuestionService $questionService;
    private L10nService $l10nService;
    private AttributesService $attributesService;
    private AnswersService $answersService;
    private SubQuestionsService $subQuestionsService;
    private Question $modelQuestion;
    private ProxyExpressionManager $proxyExpressionManager;

    public function __construct(
        QuestionService $questionService,
        L10nService $l10nService,
        AttributesService $attributesService,
        AnswersService $answersService,
        SubQuestionsService $subQuestionsService,
        Question $modelQuestion,
        ProxyExpressionManager $proxyExpressionManager
    ) {
        $this->questionService = $questionService;
        $this->l10nService = $l10nService;
        $this->attributesService = $attributesService;
        $this->answersService = $answersService;
        $this->subQuestionsService = $subQuestionsService;
        $this->modelQuestion = $modelQuestion;
        $this->proxyExpressionManager = $proxyExpressionManager;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array{
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
     *  ?questionL10n: array{
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
    public function save($input)
    {
        $data = $this->normaliseInput($input);

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

        $this->attributesService
            ->save(
                $question,
                $data['question']
            );

        if (isset($input['answeroptions'])) {
            $this->answersService->save(
                $question,
                $input['answeroptions']
            );
        }

        if (isset($input['subquestions'])) {
            $this->subQuestionsService->save(
                $question,
                $input['subquestions']
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
    public function normaliseInput($input)
    {
        $input  = $input ?? [];

        $data = [];
        $data['question']         = $input['question'] ?? [];
        $data['question']['sid']  = $data['question']['sid'] ?? ($input['sid'] ?? 0);
        $data['question']['qid']  = $data['question']['qid'] ?? null;
        $data['questionL10n']     = $input['questionL10n'] ?? [];
        $data['advancedSettings'] = $input['advancedSettings'] ?? [];
        $data['answeroptions']    = $input['answeroptions'] ?? null;
        $data['subquestions']     = $input['subquestions'] ?? null;

        if (
            !empty($data['question']['qid']) &&
            empty($data['question']['sid'])
        ) {
            $question = $this->modelQuestion
                ->findByPk($data['question']['qid']);
            if ($question) {
                $data['question']['sid'] = $question->sid;
            }
        }

        return $data;
    }
}
