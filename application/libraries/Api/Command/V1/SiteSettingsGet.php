<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;


class SiteSettingsGet implements CommandInterface
{
    /**
     * Run site settings get command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $setttingName = (string) $request->getData('setttingName');

        $apiSession = new ApiSession;
        if ($apiSession->checkKey($sessionKey)) {
            if (\Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                if (\Yii::app()->getConfig($setttingName) !== false) {
                    return new CommandResponse(\Yii::app()->getConfig($setttingName));
                } else {
                    return new CommandResponse(array('status' => 'Invalid setting'));
                }
            } else {
                return new CommandResponse(array('status' => 'Invalid setting'));
            }
        } else {
            return new CommandResponse(array('status' => ApiSession::INVALID_SESSION_KEY));
        }
    }
}
