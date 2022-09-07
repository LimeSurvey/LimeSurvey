<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;
use LimeSurvey\Api\ApiSession;

class SessionKeyCreate implements CommandInterface
{
    /**
     * Run session key create command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $apiSession = new ApiSession;

        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');
        $plugin = (string) $request->getData('plugin', 'Authdb');

        $loginResult = $apiSession->doLogin($username, $password, $plugin);
        if ($loginResult === true) {
            $apiSession->jumpStartSession($username);
            $sSessionKey = \Yii::app()->securityManager->generateRandomString(32);
            $session = new \Session();
            $session->id = $sSessionKey;
            $session->expire = time() + (int) \Yii::app()->getConfig('iSessionExpirationTime', ini_get('session.gc_maxlifetime'));
            $session->data = $username;
            $session->save();
            return $sSessionKey;
        }
        if (is_string($loginResult)) {
            return new CommandResponse(array('status' => $loginResult));
        }
        return new CommandResponse(array('status' => 'Invalid user name or password'));
    }
}
