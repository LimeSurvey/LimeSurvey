<?php

namespace LimeSurvey\Helpers\RemoteControl\Commands;

use LimeSurvey\Helpers\RemoteControl\RemoteCommandBase;

/**
 * Create and return a session key.
 *
 * Using this function you can create a new XML-RPC/JSON-RPC session key.
 * This is mandatory for all following LSRC2 function calls.
 *
 * * In case of success : Return the session key in string
 * * In case of error:
 *     * for protocol-level errors (invalid format etc), an error message.
 *     * For invalid username and password, returns a null error and the result body contains a 'status' name-value pair with the error message.
 *
 * @param string $username
 * @param string $password
 * @param string $plugin to be used
 * @return string|array
 */
class GetSessionKeyRemoteCommand extends RemoteCommandBase
{
    public function run() // $username, $password, $plugin = 'Authdb')
    {
        $username = (string) $username;
        $password = (string) $password;
        $loginResult = $this->doLogin($username, $password, $plugin);
        if ($loginResult === true) {
            $this->jumpStartSession($username);
            $sessionKey = Yii::app()->securityManager->generateRandomString(32);
            $session = new Session();
            $session->id = $sessionKey;
            $session->expire = time() + (int) Yii::app()->getConfig('iSessionExpirationTime', ini_get('session.gc_maxlifetime'));
            $session->data = $username;
            $session->save();
            return $sessionKey;
        }
        if (is_string($loginResult)) {
            return ['status' => $loginResult];
        }
        return ['status' => 'Invalid user name or password'];
    }

    /**
     * Login with username and password
     *
     * @access protected
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return bool|string
     */
    protected function doLogin($sUsername, $sPassword, $sPlugin)
    {
        /* @var $identity LSUserIdentity */
        $identity = new \LSUserIdentity($sUsername, $sPassword);
        $identity->setPlugin($sPlugin);
        $event = new \PluginEvent('remoteControlLogin');
        $event->set('identity', $identity);
        $event->set('plugin', $sPlugin);
        $event->set('username', $sUsername);
        $event->set('password', $sPassword);
        App()->getPluginManager()->dispatchEvent($event, array($sPlugin));
        if (!$identity->authenticate()) {
            if ($identity->errorMessage) {
                // don't return an empty string
                return $identity->errorMessage;
            }
            return false;
        } else {
            return true;
        }
    }

}
