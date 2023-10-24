<?php

namespace LimeSurvey\Api\Auth;

use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use CDbCriteria;
use Session;
use Yii;

class AuthSession
{
    const ERROR_INVALID_SESSION_KEY = 'INVALID_SESSION_KEY';

    /**
     * Login with username and password
     *
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return bool|string
     */
    public function doLogin($sUsername, $sPassword, $sPlugin = 'Authdb')
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
                throw new ExceptionInvalidUser($identity->errorMessage);
            }
            throw new ExceptionInvalidUser('Invalid user name or password');
        } else {
            return ($this->createSession($sUsername))->id;
        }
    }

    /**
     * Create session key
     *
     * @param string $username The username
     * @return Session
     */
    public function createSession($username)
    {
        $this->jumpStartSession($username);
        $sessionKey = (string) Yii::app()->securityManager
            ->generateRandomString(32);
        $session = new Session();
        $session->id = $sessionKey;
        $session->expire = time() + (60 * 60 * 24 * 7);
        $session->data = $username;
        $session->save();
        return $session;
    }

    /**
     * Fills the session with necessary user info on the fly
     *
     * @param string $username The username
     * @return bool
     */
    public function jumpStartSession($username)
    {
        $oUser = \User::model()->findByAttributes(array('users_name' => $username));

        if (!$oUser) {
            return false;
        }

        $aUserData = $oUser->attributes;

        /** @var \LSYii_Application */
        $app = \Yii::app();

        $session = array(
            'loginID' => intval($aUserData['uid']),
            'user' => $aUserData['users_name'],
            'full_name' => $aUserData['full_name'],
            'htmleditormode' => $aUserData['htmleditormode'],
            'templateeditormode' => $aUserData['templateeditormode'],
            'questionselectormode' => $aUserData['questionselectormode'],
            'dateformat' => $aUserData['dateformat'],
            'adminlang' => 'en'
        );
        foreach ($session as $k => $v) {
            $app->session[$k] = $v;
        }
        $app->user->setId($aUserData['uid']);

        return true;
    }

    /**
     * Check Key
     *
     * Check if the session key is valid. If yes returns true,
     * otherwise false and sends an error message with error code 1
     *
     * @param string $sSessionKey Auth credentials
     * @return bool
     */
    public function checkKey($sSessionKey)
    {
        $criteria = new \CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        \Session::model()->deleteAll($criteria);
        $oResult = \Session::model()->findByPk($sSessionKey);

        if (is_null($oResult)) {
            return false;
        } else {
            $this->jumpStartSession($oResult->data);
            return true;
        }
    }

    /**
     * Logout
     *
     * @param ?string $sessionKey
     * @return void
     */
    public function doLogout($sessionKey)
    {
        Session::model()
            ->deleteAllByAttributes(array(
                'id' => $sessionKey
            ));
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()
            ->deleteAll($criteria);
    }
}
