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

        return $this->responseFactory
            ->makeSuccess(['permissions' => $responseData]);
    }
}
