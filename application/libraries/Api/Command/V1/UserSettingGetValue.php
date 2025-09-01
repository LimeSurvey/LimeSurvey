<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use Permission;
use SettingsUser;

class UserSettingGetValue implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected Permission $permission;
    protected SettingsUser $modelSettingsUser;

    public function __construct(
        ResponseFactory $responseFactory,
        Permission $permission,
        SettingsUser $modelSettingsUser
    ) {
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
        $this->modelSettingsUser = $modelSettingsUser;
    }

    /**
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $settingsName = $request->getData('_id');
        $hasPermission = $this->permission->hasGlobalPermission('users');

        //users should only be able to get their own data (when they don't have permission)
        if (!$hasPermission) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        $settingUser = $this->modelSettingsUser::getUserSettingValue(
            $settingsName
        );

        if (!isset($settingUser)) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SETTING_NOT_FOUND',
                    'Setting not found'
                )
                )->toArray()
            );
        }

        return $this->responseFactory->makeSuccess($settingUser);
    }
}
