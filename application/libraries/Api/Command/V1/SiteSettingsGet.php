<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\ApiSession;

class SiteSettingsGet implements CommandInterface
{
    /**
     * Run site settings get command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\Request\Request $request
     * @return LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $settingName = (string) $request->getData('settingName');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sessionKey)) {
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                if (Yii::app()->getConfig($settingName) !== false) {
                    return new Response(
                        Yii::app()->getConfig($settingName), 
                        new StatusSuccess
                    );
                } else {
                    return new Response(
                        array('status' => 'Invalid setting'), 
                        new StatusErrorBadRequest
                    );
                }
            } else {
                return new Response(
                    array('status' => 'Invalid setting'), 
                    new StatusErrorBadRequest
                );
            }
        } else {
            return new Response(
                array('status' => ApiSession::INVALID_SESSION_KEY), 
                new StatusErrorBadRequest
            );
        }
    }
}
