<?php

namespace LimeSurvey\Api\Command\Auth;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Auth\ApiSession;

class CommandAuthSession implements CommandAuthInterface
{
    protected ApiSession $apiSession;

    /**
     * @param ApiSession $apiSession
     */
    public function __construct(ApiSession $apiSession)
    {
        $this->apiSession = $apiSession;
    }

    /**
     * @param Request $request
     * @return ?mixed
     */
    public function login($request)
    {
        return $this->apiSession->login(
            $request->getData('username'),
            $request->getData('password'),
            'Authdb'
        );
    }

    /**
     * @param Request $request
     * @return ?mixed
     */
    public function logout($request)
    {
        return $this->apiSession->logout(
            $this->getAuthToken($request)
        );
    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function isAuthenticated($request)
    {
        return $this->apiSession->checkKey(
            $this->getAuthToken($request)
        );
    }


    /**
     * Get auth token.
     *
     * Attempts to read from 'authToken' GET parameter and falls back to authorisation bearer token.
     *
     * @param Request $request
     * @return string|null
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
     * @return string|null
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
