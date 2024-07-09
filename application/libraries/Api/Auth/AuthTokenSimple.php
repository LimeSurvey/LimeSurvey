<?php

namespace LimeSurvey\Api\Auth;

use LimeSurvey\Api\Auth\AuthInterface;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LSUserIdentity,
    PluginEvent,
    CDbCriteria,
    Session,
    User,
    Yii;

class AuthTokenSimple implements AuthInterface
{
    const ERROR_INVALID_SESSION_KEY = 'INVALID_SESSION_KEY';

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
    private function jumpStartSession($username)
    {
        $oUser = User::model()->findByAttributes(
            array('users_name' => $username)
        );

        if (!$oUser) {
            return false;
        }

        $aUserData = $oUser->attributes;

        /** @var \LSYii_Application */
        $app = Yii::app();

        $session = array(
            'loginID' => intval($aUserData['uid']),
            'user' => $aUserData['users_name'],
            'full_name' => $aUserData['full_name'],
            'htmleditormode' => $aUserData['htmleditormode'],
            'templateeditormode' => $aUserData['templateeditormode'],
            'questionselectormode' => $aUserData['questionselectormode'],
            // When using the REST API, data is transferred using the format
            // YYYY-MM-DD since the browser handles formatting for display.
            // This format is defined as '6' in
            // insurveytranslator_helper.php / getDateFormatData()
            'dateformat' => 6,
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
            $this->jumpStartSession($oResult->data);
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
