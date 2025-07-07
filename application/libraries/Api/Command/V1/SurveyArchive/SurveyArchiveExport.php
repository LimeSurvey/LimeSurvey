<?php

namespace LimeSurvey\Api\Command\V1\SurveyArchive;

use Permission;
use LimeSurvey\Models\Services\{
    SurveyArchiveService
};
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};

class SurveyArchiveExport implements CommandInterface
{
    protected Permission $permission;

    protected ResponseFactory $responseFactory;

    protected SurveyArchiveService $surveyArchiveService;

    /**
     * Constructor
     * @param \LimeSurvey\Api\Command\Response\ResponseFactory $responseFactory
     * @param SurveyArchiveService $surveyArchiveService
     */
    public function __construct(
        Permission $permission,
        ResponseFactory $responseFactory,
        SurveyArchiveService $surveyArchiveService
    ) {
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->surveyArchiveService = $surveyArchiveService;
    }

    /**
     * Processes the request
     * @param \LimeSurvey\Api\Command\Request\Request $request
     */
    public function run(Request $request)
    {
        $timestamp = (int) $request->getData('timestamp');
        if (!$timestamp) {
            throw new \InvalidArgumentException("Missing required parameter: timestamp");
        }

        $surveyId = (int) $request->getData('_id');
        if ($response = $this->ensurePermissions($surveyId)) {
            return $response;
        }

        $archiveType = $request->getData('archiveType') ?? null;
        if ($archiveType === null) {
            throw new \InvalidArgumentException("Missing required parameter: archiveType");
        }

        $archiveExists = $this->surveyArchiveService->doesArchiveExists($surveyId, $timestamp, $archiveType);
        if (!$archiveExists) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'ARCHIVE_NOT_FOUND',
                    'Archive not found'
                )
                )->toArray()
            );
        }

        $typePartMap = [
            SurveyArchiveService::$Tokens_archive   => 'participants',
            SurveyArchiveService::$Response_archive => 'responses',
        ];

        $streamHandlerMap = [
            SurveyArchiveService::$Tokens_archive   => 'exportTokensAsStream',
            SurveyArchiveService::$Response_archive => 'exportResponsesAsStream',
        ];

        if (!isset($typePartMap[$archiveType], $streamHandlerMap[$archiveType])) {
            throw new \InvalidArgumentException("Unsupported archive type: $archiveType");
        }

        $typePart = $typePartMap[$archiveType];
        $method = $streamHandlerMap[$archiveType];

        $fileName = "survey_{$surveyId}_{$typePart}_{$timestamp}.csv";
        $contentType = 'Content-Type: text/csv; charset=utf-8';

        $streamHandler = function () use ($surveyId, $timestamp, $method): void {
            $this->surveyArchiveService->$method($surveyId, $timestamp);
        };

        return $this->responseFactory->streamResponse(
            ['fileName' => $fileName, 'contentType' => $contentType],
            $streamHandler
        );
    }

    /**
     * Ensure Permissions
     *
     * @param int $surveyId
     * @return Response|false
     */
    private function ensurePermissions($surveyId)
    {
        if (!$surveyId) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }

        if (
            !$this->permission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'read'
            )
        ) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        return false;
    }
}
