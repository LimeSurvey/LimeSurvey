<?php

namespace LimeSurvey\Api\Command\V1;

use User;
use Permission;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyOwner;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthTokenSimple;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class UserList implements CommandInterface
{
    use AuthPermissionTrait;

    protected User $user;
    protected Permission $permission;
    protected AuthTokenSimple $authTokenSimple;
    protected TransformerOutputSurveyOwner $transformerOutputSurveyOwner;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param User $user
     * @param Permission $permission
     * @param AuthTokenSimple $authTokenSimple
     * @param TransformerOutputSurveyOwner $transformerOutputSurveyOwner
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        User $user,
        Permission $permission,
        AuthTokenSimple $authTokenSimple,
        TransformerOutputSurveyOwner $transformerOutputSurveyOwner,
        ResponseFactory $responseFactory
    ) {
        $this->user = $user;
        $this->permission = $permission;
        $this->authTokenSimple = $authTokenSimple;
        $this->transformerOutputSurveyOwner = $transformerOutputSurveyOwner;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run user list command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $authToken = (string) $request->getData('authToken');

        if (
            !$this->authTokenSimple
                ->isAuthenticated($authToken)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        if (!$this->permission->hasGlobalPermission('users', 'read')) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        $dataProvider = $this->user
            ->search();

        $users = $this->transformerOutputSurveyOwner
            ->transformAll($dataProvider->getData());

        return $this->responseFactory
            ->makeSuccess(['users' => $users]);
    }
}
