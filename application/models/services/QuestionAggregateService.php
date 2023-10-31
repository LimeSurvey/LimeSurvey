<?php

namespace LimeSurvey\Models\Services;

use Permission;
use Question;
use CDbConnection;
use LimeSurvey\Models\Services\{
    QuestionAggregateService\SaveService,
    QuestionAggregateService\DeleteService,
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
class QuestionAggregateService
{
    private SaveService $saveService;
    private DeleteService $deleteService;
    private Permission $modelPermission;
    private CDbConnection $yiiDb;

    public function __construct(
        SaveService $saveService,
        DeleteService $deleteService,
        Permission $modelPermission,
        CDbConnection $yiiDb
    ) {
        $this->saveService = $saveService;
        $this->deleteService = $deleteService;
        $this->modelPermission = $modelPermission;
        $this->yiiDb = $yiiDb;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param int $surveyId
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
     * @return Question
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function save($surveyId, $input)
    {
        $this->checkUpdatePermission($surveyId);
        $transaction = $this->yiiDb->beginTransaction();
        try {
            $question = $this->saveService->save(
                $surveyId,
                $input
            );
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }

        return $question;
    }

    /*
     * @param int $questionIds
     * @throws PersistErrorException
     * @throws QuestionHasConditionsException
     * @return void
     */
    public function delete($surveyId, $questionId)
    {
        $this->deleteMany($surveyId, [$questionId]);
    }

    /*
     * @param array $questionIds
     * @throws PersistErrorException
     * @throws QuestionHasConditionsException
     * @return void
     */
    public function deleteMany($surveyId, $questionIds)
    {
        if (
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'delete'
            )
        ) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }

        $transaction = $this->yiiDb->beginTransaction();
        try {
            foreach ($questionIds as $questionId) {
                $this->deleteService->delete($surveyId, $questionId);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param $surveyId
     * @return void
     * @throws PermissionDeniedException
     */
    public function checkUpdatePermission($surveyId): void
    {
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

    /**
     * Delete answer from a question.
     * All language entries for this answer will be deleted.
     * @param $surveyId
     * @param $answerId
     * @return void
     */
    public function deleteAnswer($surveyId, $answerId)
    {
        if (
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'delete'
            )
        ) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }

        $transaction = $this->yiiDb->beginTransaction();
        try {
            $this->deleteService->deleteAnswer($answerId);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
}
