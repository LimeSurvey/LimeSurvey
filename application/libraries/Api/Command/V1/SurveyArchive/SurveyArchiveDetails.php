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

class SurveyArchiveDetails implements CommandInterface
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
        $surveyId = (int) $request->getData('_id');

        $timestamp = (int) $request->getData('timestamp');
        if (!$timestamp) {
            throw new \InvalidArgumentException("Missing required parameter: timestamp");
        }

        $archiveType = $request->getData('archiveType', '');
        if (!in_array($archiveType, [SurveyArchiveService::$Response_archive, SurveyArchiveService::$Tokens_archive], true)) {
            throw new \InvalidArgumentException("Invalid archive type");
        }

        if ($response = $this->ensurePermissions($surveyId)) {
            return $response;
        }

        $searchParams = [
            'filters' => $request->getData('filters', []),
            'sort' => $request->getData('sort', []),
            'page' => (int) $request->getData('page', 1),
            'pageSize' => (int) $request->getData('pageSize', 10),
        ];

        switch ($archiveType) {
            case SurveyArchiveService::$Response_archive:
                $data = $this->surveyArchiveService->getResponseArchiveData($surveyId, $timestamp, $searchParams);
                break;
            case SurveyArchiveService::$Tokens_archive:
                $data = $this->surveyArchiveService->getTokenArchiveData($surveyId, $timestamp, $searchParams);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported archive type");
        }

        return $this->responseFactory->makeSuccess([
            'archiveType' => $archiveType,
            'result' => $data,
        ]);    
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
