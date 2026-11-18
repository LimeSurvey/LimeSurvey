<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory};
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputUserPermissions;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class UserPermission implements CommandInterface
{
    use AuthPermissionTrait;

    protected Permission $permission;
    protected TransformerOutputUserPermissions $transformOutputUserPermissions;
    protected ResponseFactory $responseFactory;

    /**
     * Creates the user permission command.
     *
     * @param Permission $permission The permission model used for the lookups.
     * @param TransformerOutputUserPermissions $transformOutputUserPermissions Transforms the user's permission rows for the response.
     * @param ResponseFactory $responseFactory Builds the command response.
     */
    public function __construct(
        Permission $permission,
        TransformerOutputUserPermissions $transformOutputUserPermissions,
        ResponseFactory $responseFactory
    ) {
        $this->permission = $permission;
        $this->transformOutputUserPermissions = $transformOutputUserPermissions;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Returns the current user's permissions, plus the effective permissions on a survey when a surveyId is given.
     *
     * @param Request $request The request, optionally with a surveyId.
     * @return Response The success response with the permissions.
     */
    public function run(Request $request)
    {
        $userId = App()->user->getId();

        $permissions = $this->permission->getPermissions($userId);
        $responseData = $this->transformOutputUserPermissions->transform($permissions);

        $surveyId = (int) $request->getData('surveyId');
        if ($surveyId > 0) {
            $responseData['effective'] = $this->getEffectiveSurveyPermissions($surveyId);
        }

        return $this->responseFactory
            ->makeSuccess(['permissions' => $responseData]);
    }

    /**
     * Resolves the current user's effective permissions on a survey, including
     * owner, global and survey group inheritance.
     *
     * The survey-level "Survey" permission has no update right, so surveyUpdate
     * is true when the user can update the survey content or the survey settings.
     * The classic editor checks these two on their own pages; the new editor has
     * only this one flag, and the server still checks each change.
     *
     * @param int $surveyId The survey to resolve the permissions for.
     * @return array<string, bool> The flags surveyRead, surveyUpdate, responsesRead and responsesUpdate.
     */
    private function getEffectiveSurveyPermissions(int $surveyId): array
    {
        $can = fn(string $permission, string $crud): bool =>
            $this->permission->hasSurveyPermission($surveyId, $permission, $crud);

        return [
            'surveyRead' => $can('survey', 'read'),
            'surveyUpdate' => $can('surveycontent', 'update')
                || $can('surveysettings', 'update'),
            'responsesRead' => $can('responses', 'read')
                || $can('responses', 'update')
                || $can('statistics', 'read'),
            'responsesUpdate' => $can('responses', 'update'),
        ];
    }
}
