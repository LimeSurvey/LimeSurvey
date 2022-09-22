<?php

namespace LimeSurvey\Api\Command\V1;

use Session;
use Yii;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;
use LimeSurvey\Api\Command\Response\Status\StatusErrorBadRequest;
use LimeSurvey\Api\ApiSession;

class SessionKeyCreate implements CommandInterface
{
    /**
     * Run session key create command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $apiSession = new ApiSession();

        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');
        $plugin = (string) $request->getData('plugin', 'Authdb');

        $loginResult = $apiSession->doLogin($username, $password, $plugin);
        if ($loginResult === true) {
            $apiSession->jumpStartSession($username);
            $sSessionKey = Yii::app()->securityManager->generateRandomString(32);
            $session = new Session();
            $session->id = $sSessionKey;
            $session->expire = time() + (int) Yii::app()->getConfig('iSessionExpirationTime', ini_get('session.gc_maxlifetime'));
            $session->data = $username;
            $session->save();
            return new Response(
                $sSessionKey,
                new StatusSuccess()
            );
        }
        if (is_string($loginResult)) {
            return new Response(
                array('status' => $loginResult),
                new StatusSuccess()
            );
        }
        return new Response(
            array(
                'status' => 'Invalid user name or password',
                new StatusErrorBadRequest()
            )
        );
    }
}
