<?php

namespace LimeSurvey\Api\Authentication;

use LimeSurvey\Api\Authentication\AuthenticationInterface;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LSUserIdentity;
use PluginEvent;
use CDbCriteria;
use Session;
use Yii;

class AuthenticationTokenSimple implements AuthenticationInterface
{
    const ERROR_INVALID_SESSION_KEY = 'INVALID_SESSION_KEY';

    protected SessionUtil $sessionUtil;

    /**
     * Constructor
     *
     * @param SessionUtil $sessionUtil
     */
    public function __construct(SessionUtil $sessionUtil)
    {
        $this->sessionUtil = $sessionUtil;
    }

    /**
     * Login with username and password
     *
     * @param string $username
     * @param string $password
     * @return bool|string
     */
    public function login($username, $password)
    {
        /* @var $identity LSUserIdentity */
        $plugin = 'Authdb';
        $identity = new LSUserIdentity($username, $password);
        $identity->setPlugin($plugin);
        $event = new PluginEvent('remoteControlLogin');
        $event->set('identity', $identity);
        $event->set('plugin', $plugin);
        $event->set('username', $username);
        $event->set('password', $password);
        App()->getPluginManager()->dispatchEvent($event, array($plugin));
        if (!$identity->authenticate()) {
            if ($identity->errorMessage) {
                // don't return an empty string
                throw new ExceptionInvalidUser($identity->errorMessage);
            }
            throw new ExceptionInvalidUser('Invalid user name or password');
        } else {
            return ($this->createSession($username))->id;
        }
    }

    /**
     * Refresh token
     *
     * @param string $token
     * @return bool|string
     */
    public function refresh($token)
    {
        $existingSession = Session::model()->findByPk($token);

        $result = $this->createSession($existingSession->data)->id;

        // Expire existing token
        $existingSession->expire = time() - 1;
        $existingSession->save();

        return $result;
    }

    /**
     * Create session key
     *
     * @param string $username The username
     * @return Session
     */
    public function createSession($username)
    {
        $this->sessionUtil->jumpStartSession($username);
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
     * Check Key
     *
     * Check if the session key is valid. If yes returns true,
     * otherwise false and sends an error message with error code 1
     *
     * @param string $token
     * @return bool
     */
    public function isAuthenticated($token)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()->deleteAll($criteria);
        $oResult = Session::model()->findByPk($token);

        if (is_null($oResult)) {
            return false;
        } else {
            $this->sessionUtil->jumpStartSession($oResult->data);
            return true;
        }
    }

    /**
     * Logout
     *
     * @param string $token
     * @return void
     */
    public function logout($token)
    {
        Session::model()
            ->deleteAllByAttributes(array(
                'id' => $token
            ));
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()
            ->deleteAll($criteria);
    }
}
