<?php

namespace LimeSurvey\Api\Command\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use User;
use LimeSurvey\Auth\{
    AuthCommon,
    AuthSession
};
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\Request\Request;

class CommandAuthJwt implements CommandAuthInterface
{
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
    ) {
        $this->authCommon = $authCommon;
        $this->authSession = $authSession;
    }

    /**
     * Login with username and password or refresh bearer token
     *
     * @param Request $request
     * @return ?mixed
     */
    public function login($request)
    {
        // If requested with a valid refresh token generate a new tokens
        $jwt = null;
        try {
            $jwt = $this->getDecodedJwt($request);
        } catch (\Throwable $e) {
            // not authenticated
        }
        if ($jwt && !empty($jwt->re)) {
            $user = User::model()->find($jwt->uid);
            if ($user) {
                return $this->getResponseSuccess($user);
            }
        }

        $identity = $this->authCommon->getIdentity(
            $request->getData('username') ?: '',
            $request->getData('password') ?: ''
        );


        if (!$identity->authenticate()) {
            if ($identity->errorMessage) {
                throw new ExceptionInvalidUser($identity->errorMessage);
            }
        } else {
            return $this->getResponseSuccess($identity->getUser());
        }

        throw new ExceptionInvalidUser('Invalid user name or password');
    }

    /**
     * @param Request $request
     * @return ?mixed
     */
    public function logout($request)
    {
        return true;
    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function isAuthenticated($request)
    {
        $jwt = $this->getDecodedJwt($request);
        if (
            !empty($jwt->uid)
            && empty($jwt->re) // refresh token can not be used for authentication
        ) {
            // We have to do this because legacy code expects user data
            // to exist in a PHP session. If we can refactor legacy code
            // to read user data directly from the JWT this would not be necessary.
            return $this->authSession->initSessionByUid($jwt->uid);
        }
        return false;
    }

    /**
     * Get response success
     *
     * @param User $user
     * @return array
     */
    private function getResponseSuccess(User $user)
    {
        $jsDateFormat = 'Y-m-d\TH:i:s.00\Z';

        $accessTokenExpiresUnixTime = strtotime('+30 mins');
        $accessTokenExpiresJs = gmdate(
            $jsDateFormat,
            $accessTokenExpiresUnixTime
        );

        $refreshTokenLifeExpiresUnixTime = strtotime('+3 months');
        $refreshTokenExpiresJs = gmdate(
            $jsDateFormat,
            $refreshTokenLifeExpiresUnixTime
        );

        return [
            'accessToken' => [
                'token' => $this->createAccessToken(
                    $user,
                    $accessTokenExpiresUnixTime
                ),
                'expires' => $accessTokenExpiresJs
            ],
            'refreshToken' => [
                'token' => $this->createRefreshToken(
                    $user,
                    $refreshTokenLifeExpiresUnixTime
                ),
                'expires' => $refreshTokenExpiresJs
            ],
        ];
    }

    /**
     * Create access token
     *
     * @param User $user
     * @param int $expires UnixTime expiration - short lived
     * @return string
     */
    private function createAccessToken(User $user, $expires)
    {
        $payload = [
            'iat' => time(),
            'exp' => $expires, // + 30 minutes
            'uid' => $user->uid,
        ];
        return JWT::encode($payload, $this->getPrivateKey(), 'HS256');
    }

    /**
     * Create refresh token
     *
     * @param User $user
     * @param int $expires UnixTime expiration - long lived
     * @return string
     */
    private function createRefreshToken(User $user, $expires)
    {
        $payload = [
            'iat' => time(),
            'exp' => $expires, // + 3 months
            'uid' => $user->uid,
            're' => true // indicate this is a refresh token
        ];
        return JWT::encode($payload, $this->getPrivateKey(), 'HS256');
    }
    /**
     * Get decoded JWT
     *
     * @param Request $request
     * @return object
     */
    private function getDecodedJwt(Request $request)
    {
        JWT::$leeway = 60; // $leeway in seconds
        return JWT::decode(
            $this->getAuthToken($request) ?: '',
            new Key($this->getPrivateKey(), 'HS256')
        );
    }

    /**
     * Get JWT private key
     *
     * This must be unique for each installation.
     *
     * @param Request $request
     * @return string
     */
    private function getPrivateKey()
    {
        return '0%WzPWb8YDMEhUaQEQx9XbMk9x8gbD#s';
    }

    /**
     * Get auth token.
     *
     * Attempts to read from 'authToken' GET parameter
     * - and falls back to authorisation bearer token.
     *
     * @param Request $request
     * @return ?string
     */
    private function getAuthToken(Request $request)
    {
        $token = $request->getData('authToken');
        if (!$token) {
            $token = $this->getAuthBearerToken($request);
        }
        return $token;
    }

    /**
     * Get auth bearer token.
     *
     * Attempts to read bearer token from authorisation header.
     *
     * @param Request $request
     * @return ?string
     */
    private function getAuthBearerToken(Request $request)
    {
        $authHeader = $request->getData('Authorization');
        $token = null;
        if (
            !empty($authHeader)
            && strpos(
                $authHeader,
                'Bearer '
            ) === 0
        ) {
            $token = substr($authHeader, 7);
        }

        return $token;
    }
}
