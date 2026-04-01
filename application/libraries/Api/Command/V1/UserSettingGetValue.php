<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use SettingsUser;

class UserSettingGetValue implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected SettingsUser $modelSettingsUser;

    public function __construct(
        ResponseFactory $responseFactory,
        SettingsUser $modelSettingsUser
    ) {
        $this->responseFactory = $responseFactory;
        $this->modelSettingsUser = $modelSettingsUser;
    }

    /**
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $settingName = (string)$request->getData('_id');
        $settingUser = $this->modelSettingsUser::getUserSettingValue(
            $settingName
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
