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

class UserSettingSetValue implements CommandInterface
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
        $settingName = (string)$request->getData('settingName');
        $settingValue = (string)$request->getData('settingValue');
        $hasPermission = $this->permission->hasGlobalPermission('users');

        //users should only be able to get their own data (when they don't have permission)
        if (!$hasPermission) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        $settingsUser = $this->modelSettingsUser::setUserSetting(
            $settingName,
            $settingValue
        );

        if (!$settingsUser) {
            return $this->responseFactory->makeErrorBadRequest(
                (new ResponseDataError(
                    'SETTINGS_NOT_SAVED',
                    'Settings not saved'
                )
                )->toArray()
            );
        }

        return $this->responseFactory->makeSuccess($settingsUser);
    }
}
