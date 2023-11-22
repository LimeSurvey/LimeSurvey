<?php

namespace LimeSurvey\Api\Auth;

use LimeSurvey\Auth\{
    AuthCommon,
    AuthSession
};
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use CDbCriteria;
use Session;
use Yii;

class ApiSession
{
    const ERROR_INVALID_SESSION_KEY = 'INVALID_SESSION_KEY';

    protected AuthCommon $authCommon;
    protected AuthSession $authSession;

    /**
     * Constructor
     *
     * @param array $config
     * @param array $commandParams
     * @param ContainerInterface $diContainer
     * @return string|null
     */
    public function __construct(
        AuthCommon $authCommon,
        AuthSession $authSession
    )
    {
        $this->authCommon = $authCommon;
        $this->authSession = $authSession;
    }

    /**
     * Login with username and password
     *
     * @param string $sUsername username
     * @param string $sPassword password
     * @return bool|string
     */
    public function login($sUsername, $sPassword)
    {
        $identity = $this->authCommon->getIdentity(
            $sUsername,
            $sPassword
        );
        if (!$identity->authenticate()) {
            if ($identity->errorMessage) {
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
        $this->authSession->initSessionByUsername($username);
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
            $this->authSession->initSessionByUsername($oResult->data);
            return true;
        }
    }

    /**
     * Logout
     *
     * @param ?string $sessionKey
     * @return void
     */
    public function logout($sessionKey)
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
