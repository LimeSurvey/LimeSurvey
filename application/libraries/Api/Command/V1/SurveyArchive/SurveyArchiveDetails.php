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

        if ($response = $this->ensurePermissions($surveyId, $archiveType)) {
            return $response;
        }

        $searchParams = [
            'filters' => $this->decodeArrayParam($request->getData('filters', [])),
            'sort' => $this->decodeArrayParam($request->getData('sort', [])),
            'page' => (int) $request->getData('page', 1),
            'pageSize' => (int) $request->getData('pageSize', 10),
        ];

        $archiveTypeMap = [
            SurveyArchiveService::$Response_archive => 'getResponseArchiveData',
            SurveyArchiveService::$Tokens_archive   => 'getTokenArchiveData',
        ];

        $method = $archiveTypeMap[$archiveType];
        try {
            $data = $this->surveyArchiveService->$method($surveyId, $timestamp, $searchParams);
        } catch (\InvalidArgumentException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                (new ResponseDataError('INVALID_PARAMETER', $e->getMessage()))->toArray()
            );
        }

        return $this->responseFactory->makeSuccess([
            'archiveType' => $archiveType,
            'result' => $data,
        ]);
    }

    /**
     * Decodes a request parameter that may be sent either as array or as JSON string
     *
     * @param mixed $value
     * @return array
     */
    private function decodeArrayParam($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        return is_array($value) ? $value : [];
    }

    /**
     * Ensure Permissions
     *
     * Reading archived data requires read permission on the archived data itself
     * (responses or participants), not only on the survey content.
     *
     * @param int $surveyId
     * @param string $archiveType
     * @return Response|false
     */
    private function ensurePermissions($surveyId, string $archiveType)
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
                $archiveType === SurveyArchiveService::$Tokens_archive ? 'tokens' : 'responses',
                'read'
            )
        ) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        return false;
    }
}
