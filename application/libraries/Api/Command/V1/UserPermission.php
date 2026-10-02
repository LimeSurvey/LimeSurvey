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
     * Constructor
     *
     * @param Permission $permission
     * @param TransformerOutputUserPermissions $transformOutputUserPermissions
     * @param AuthTokenSimple $auth
     * @param ResponseFactory $responseFactory
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
     * Run user permission command
     *
     * @param Request $request
     * @return Response
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
     * Resolved flags incl. owner, global and survey group inheritance.
     *
     * @param int $surveyId
     * @return array<string, bool>
     */
    private function getEffectiveSurveyPermissions(int $surveyId): array
    {
        $can = fn(string $permission, string $crud): bool =>
            $this->permission->hasSurveyPermission($surveyId, $permission, $crud);

        return [
            'surveyRead' => $can('survey', 'read'),
            'surveyUpdate' => $can('survey', 'update'),
            'responsesRead' => $can('responses', 'read')
                || $can('responses', 'update')
                || $can('statistics', 'read'),
            'responsesUpdate' => $can('responses', 'update'),
        ];
    }
}
