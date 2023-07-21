<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Question;
use CDbConnection;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorQuestion,
    QuestionEditorL10n,
    QuestionEditorAttributes,
    QuestionEditorAnswers,
    QuestionEditorSubQuestions
};

use LimeSurvey\Models\Services\Proxy\{
    ProxyExpressionManager
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * Question Editor Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class QuestionEditor
{
    private QuestionEditorQuestion $questionEditorQuestion;
    private QuestionEditorL10n $questionEditorL10n;
    private QuestionEditorAttributes $questionEditorAttributes;
    private QuestionEditorAnswers $questionEditorAnswers;
    private QuestionEditorSubQuestions $questionEditorSubQuestions;
    private Permission $modelPermission;
    private Question $modelQuestion;
    private ProxyExpressionManager $proxyExpressionManager;
    private CDbConnection $yiiDb;

    public function __construct(
        QuestionEditorQuestion $questionEditorQuestion,
        QuestionEditorL10n $questionEditorL10n,
        QuestionEditorAttributes $questionEditorAttributes,
        QuestionEditorAnswers $questionEditorAnswers,
        QuestionEditorSubQuestions $questionEditorSubQuestions,
        Question $modelQuestion,
        Permission $modelPermission,
        ProxyExpressionManager $proxyExpressionManager,
        CDbConnection $yiiDb
    ) {
        $this->questionEditorQuestion = $questionEditorQuestion;
        $this->questionEditorL10n = $questionEditorL10n;
        $this->questionEditorAttributes = $questionEditorAttributes;
        $this->questionEditorAnswers = $questionEditorAnswers;
        $this->questionEditorSubQuestions = $questionEditorSubQuestions;
        $this->modelQuestion = $modelQuestion;
        $this->modelPermission = $modelPermission;
        $this->proxyExpressionManager = $proxyExpressionManager;
        $this->yiiDb = $yiiDb;
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

        $question = $this->modelQuestion
            ->findByPk((int) $data['question']['qid']);
        if ($question) {
            $data['question']['sid'] = $$question->sid;
        }

        $this->checkPermissions($data['question']['sid']);

        // Rollback at failure.
        $transaction = $this->yiiDb->beginTransaction();
        try {
            $question = $this->questionEditorQuestion
                ->save($data);

            $this->questionEditorL10n->save(
                $question->qid,
                $data['questionL10n']
            );

            $this->questionEditorAttributes
                ->saveAdvanced(
                    $question,
                    $data['advancedSettings']
                );

            $this->questionEditorAttributes
                ->save(
                    $question,
                    $data['question']
                );

            if (isset($input['answeroptions'])) {
                $this->questionEditorAnswers->save(
                    $question,
                    $input['answeroptions']
                );
            }

            if (isset($input['subquestions'])) {
                $this->questionEditorSubQuestions->save(
                    $question,
                    $input['subquestions']
                );
            }

            $transaction->commit();
            // All done, redirect to edit form.
            $question->refresh();
            $this->proxyExpressionManager->setDirtyFlag();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        return $question;
    }

    /**
     * Normalise input
     *
     * @param array
     * @return array
     */
    private function normaliseInput($input)
    {
        $input  = $input ?? [];

        $data = [];
        $data['question']         = $input['question'] ?? [];
        $data['question']['sid']  = $input['sid'] ?? 0;
        $data['question']['qid']  = $data['question']['qid'] ?? null;
        $data['questionL10n']     = $input['questionL10n'] ?? [];
        $data['advancedSettings'] = $input['advancedSettings'] ?? [];
        $data['answeroptions']    = $input['answeroptions'] ?? null;
        $data['subquestions']     = $input['subquestions'] ?? null;

        return $data;
    }

    /**
     * Check permissions
     *
     * @param int $surveyId
     */
    private function checkPermissions($surveyId)
    {
        // Different permission check when sid vs qid is given.
        // This double permission check is needed if user manipulates the post data.
        if (
            !$this->modelPermission->hasSurveyPermission(
            $surveyId,
            'surveycontent',
            'update'
            )
        ) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
    }
}
