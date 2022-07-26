<?php

class GetSessionKeyRemoteCommand extends RemoteCommandBase
{
    public function run()
    {
        $username = (string) $username;
        $password = (string) $password;
        $loginResult = $this->_doLogin($username, $password, $plugin);
        if ($loginResult === true) {
            $this->_jumpStartSession($username);
            $sSessionKey = Yii::app()->securityManager->generateRandomString(32);
            $session = new Session();
            $session->id = $sSessionKey;
            $session->expire = time() + (int) Yii::app()->getConfig('iSessionExpirationTime', ini_get('session.gc_maxlifetime'));
            $session->data = $username;
            $session->save();
            return $sSessionKey;
        }
        if (is_string($loginResult)) {
            return array('status' => $loginResult);
        }
        return array('status' => 'Invalid user name or password');
    }
}
