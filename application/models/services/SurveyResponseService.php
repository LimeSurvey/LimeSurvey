<?php

namespace LimeSurvey\Models\Services;

use CDbConnection;
use CDbException;
use CException;
use LimeSurvey\Models\Services\{Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    Exception\QuestionHasConditionsException,
    QuestionAggregateService\DeleteService,
    QuestionAggregateService\SaveService};
use Permission;

/**
 * Question Aggregate Delete Service
 */
class SurveyResponseService
{
    protected CDbConnection $yiiDb;

    protected Permission $permission;

    public function __construct(CDbConnection $yiiDb, Permission $permission)
    {
        $this->yiiDb = $yiiDb;
        $this->permission = $permission;
    }

    /**
     * @param $surveyId
     * @param $rid
     * @return void
     * @throws CDbException
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    public function deleteResponse($surveyId, $rid): void
    {
        if (!$this->permission->hasSurveyPermission($surveyId, 'responses', 'delete')) {
            throw new NotFoundException('Survey not found');
        }

        try {
            $responseModel = \Response::model($surveyId);
        } catch (\Exception $e) {
            throw new NotFoundException('Survey not found');
        }

        $response = $responseModel->findByPk($rid);

        if (!$response) {
            throw new NotFoundException('Response not found');
        }

        if (!$response->delete(true)) {
            throw new PersistErrorException();
        }
    }

    /**
     * @param $surveyId
     * @param $questionIds
     * @return void
     * @throws Exception\QuestionHasConditionsException
     * @throws PersistErrorException
     * @throws CDbException|CException|NotFoundException
     */
    public function delete($surveyId, $questionIds): void
    {
        $transaction = $this->yiiDb->beginTransaction();
        try {
            foreach ($questionIds as $questionId) {
                $this->deleteResponse($surveyId, $questionId);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param int $surveyId
     * @param $responseId
     * @param $responseData
     * @throws NotFoundException
     * @throws PersistErrorException
     * @throws \Exception
     */
    public function updateResponse($surveyId, $responseId, $responseData): void
    {
        if (!$this->permission->hasSurveyPermission($surveyId, 'responses', 'update')) {
            throw new NotFoundException('Survey not found');
        }

        $responseModel = \Response::model($surveyId);
        $response = $responseModel->findByPk($responseId);

        if (!$response) {
            throw new NotFoundException('Response not found');
        }

        $response->setAllAttributes($responseData);
        if (!$response->save()) {
            throw new PersistErrorException();
        }
    }


    /**
     * @throws CException
     */
    public function update($surveyId, $responseId, $responseData): void
    {
        $transaction = $this->yiiDb->beginTransaction();
        try {
            $this->updateResponse($surveyId, $responseId, $responseData);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param $surveyId
     * @param $rid
     * @return void
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    public function deleteAttachments($surveyId, $rid): void
    {
        if (!$this->permission->hasSurveyPermission($surveyId, 'responses', 'update')) {
            throw new NotFoundException('Survey not found');
        }

        try {
            $responseModel = \Response::model($surveyId);
        } catch (\Exception $e) {
            throw new NotFoundException('Survey not found');
        }

        $response = $responseModel->findByPk($rid);

        if (!$response) {
            throw new NotFoundException('Response not found');
        }

        [$success] = $response->deleteFilesAndFilename();
        if (!$success) {
            throw new PersistErrorException('Could not delete response file');
        }
    }
}
