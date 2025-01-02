<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyOwner;
use LimeSurvey\Api\Transformer\TransformerException;
use Permission;
use User;

class UserDetail implements CommandInterface
{
    use AuthPermissionTrait;

    protected TransformerOutputSurveyOwner $transformerOutputSurveyOwner;
    protected ResponseFactory $responseFactory;
    protected Permission $permission;
    protected User $modelUser;

    public function __construct(
        TransformerOutputSurveyOwner $transformerOutputSurveyOwner,
        ResponseFactory $responseFactory,
        Permission $permission,
        User $modelUser
    ) {
        $this->transformerOutputSurveyOwner = $transformerOutputSurveyOwner;
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
        $this->modelUser = $modelUser;
    }

    /**
     *
     * @param Request $request
     * @return Response
     * @throws TransformerException
     */
    public function run(Request $request)
    {
        $userId = $request->getData('_id');
        $hasPermission = $this->permission->hasGlobalPermission('users');
        //users should only be able to get their own data (when they don't have permission)
        if (App()->user->getId() !== $userId && !$hasPermission) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        $userModel = $this->modelUser->findByAttributes(['uid' => (int)$userId]);

        if (!$userModel) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'USER_NOT_FOUND',
                    'User not found'
                )
                )->toArray()
            );
        }

        $user = $this->transformerOutputSurveyOwner->transform($userModel);

        return $this->responseFactory->makeSuccess(['user' => $user]);
    }
}
