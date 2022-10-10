<?php

namespace LimeSurvey\Api\Command\V1;

use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthGlobalPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class SiteSettingsGet implements CommandInterface
{
    use AuthSession;
    use AuthGlobalPermission;
    use CommandResponse;

    /**
     * Run site settings get command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $settingName = (string) $request->getData('settingName');

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        if (
            ($response = $this->hasGlobalPermission(
                'superadmin',
                'read'
            )
            ) !== true
        ) {
            return $response;
        }

        if (Yii::app()->getConfig($settingName) !== false) {
            return $this->responseSuccess(
                Yii::app()->getConfig($settingName)
            );
        } else {
            return $this->responseErrorBadRequest(
                array('status' => 'Invalid setting')
            );
        }
    }
}
