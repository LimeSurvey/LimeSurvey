<?php

namespace LimeSurvey\Api\Authentication;

use LimeSurvey\Api\Authentication\AuthenticationInterface;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Transformer\Formatter\FormatterDateTimeToJson;
use LSUserIdentity;
use PluginEvent;
use CDbCriteria;
use Session;
use User;
use UserApiToken;
use Yii;

class AuthenticationTokenSimple implements AuthenticationInterface
{
    const ERROR_INVALID_SESSION_KEY = 'INVALID_SESSION_KEY';

    protected SessionUtil $sessionUtil;
    protected FormatterDateTimeToJson $formatterDateTimeToJson;

    /**
     * Constructor
     *
     * @param SessionUtil $sessionUtil
     */
    public function __construct(
        SessionUtil $sessionUtil,
        FormatterDateTimeToJson $formatterDateTimeToJson
    ) {
        $this->sessionUtil = $sessionUtil;
        $this->formatterDateTimeToJson = $formatterDateTimeToJson;
    }

    /**
     * Login with username and password, or with username and API token
     *
     * A session opened with an API token only has the permissions of the token.
     *
     * @param string $username
     * @param string $password The password or an API token
     * @return array<array-key, mixed>
     */
    public function login($username, $password)
    {
        /* @var $identity LSUserIdentity */
        $plugin = 'Authdb';
        $identity = new LSUserIdentity($username, $password);
        $identity->setPlugin($plugin);
        $identity->isApiLogin = true;
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
            $session = $this->createSession($username, $identity->apiTokenId);
            return $this->getTokenData(
                $session,
                $identity->getId()
            );
        }
    }
    /**
     * Get Token Data
     *
     * @param Session $session
     * @param string|int $userId
     * @return array<array-key, mixed>
     */
    public function getTokenData($session, $userId)
    {
        return [
            'token' => $session->id,
            'created' => $this->formatterDateTimeToJson->format('now'),
            'expires' => $this->formatterDateTimeToJson->format(
                '@' . $session->expire
            ),
            'userId' => (int) $userId,
        ];
    }

    /**
     * Refresh token
     *
     * @param string $token
     * @return array<array-key, mixed>
     */
    public function refresh($token)
    {
        $existingSession = Session::model()->findByPk($token);

        if (!$existingSession || empty($existingSession->data)) {
            throw new ExceptionInvalidUser('Invalid token');
        }
        $user = User::findByUsername($existingSession->data);
        if (!$user || !UserApiToken::activateForSession($existingSession, $user)) {
            throw new ExceptionInvalidUser('Invalid token');
        }

        /** @var \LSYii_Application */
        $app = Yii::app();

        $session = $this->createSession($existingSession->data, $existingSession->api_token_id);
        $result = $this->getTokenData(
            $session,
            $app->user->id
        );

        // Expire existing token
        $existingSession->expire = time() + 5;
        $existingSession->save();

        return $result;
    }

    /**
     * Create session key
     *
     * @param string $username The username
     * @param int|null $apiTokenId ID of the API token the session is opened with, null if the password was used
     * @return Session
     */
    public function createSession($username, $apiTokenId = null)
    {
        $this->sessionUtil->jumpStartSession($username);
        $sessionKey = (string) Yii::app()->securityManager
            ->generateRandomString(32);
        $session = new Session();
        $session->id = $sessionKey;
        $session->expire = time() + (60 * 60 * 24 * 7);
        $session->data = $username;
        $session->api_token_id = $apiTokenId;
        $session->save();
        return $session;
    }

    /**
     * Check Key
     *
     * Check if the session key is valid. If yes returns true,
     * otherwise false and sends an error message with error code 1
     *
     * If the session was opened with an API token, the permissions are limited to the ones of the token
     * from now on. The session is no longer valid once the token is revoked or expired.
     *
     * @param string $token
     * @return bool True if the session key is valid
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
            $user = User::findByUsername($oResult->data);
            return $user !== null && UserApiToken::activateForSession($oResult, $user);
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
