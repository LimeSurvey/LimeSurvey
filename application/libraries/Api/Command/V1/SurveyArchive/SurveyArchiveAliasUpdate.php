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

class SurveyArchiveAliasUpdate implements CommandInterface
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

        $alias = (string) $request->getData('alias', null);
        if (!$alias) {
            throw new \InvalidArgumentException("Missing required parameter: alias");
        }

        $surveyId = (int) $request->getData('_id');
        if ($response = $this->ensurePermissions($surveyId)) {
            return $response;
        }

        try {
            $this->surveyArchiveService->updateArchiveAlias($surveyId, $timestamp, $alias);
        } catch (\Exception $e) { 
            return $this->responseFactory->makeException($e);
        }

        return $this->responseFactory->makeSuccessNoContent();
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
                'surveysettings',
                'update'
            )
        ) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        return false;
    }
}
