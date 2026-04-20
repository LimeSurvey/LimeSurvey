<?php

namespace LimeSurvey\Api\Authentication;

use LimeSurvey\Api\Authentication\ApiAuthenticationException;

interface AuthenticationInterface
{
    /**
     * Login with username and password
     *
     * @param string $username
     * @param string $password
     * @return ?mixed Response data
     * @throws ApiAuthenticationException
     */
    public function login($username, $password);

    /**
     * Refresh authentication token
     *
     * @param string $token
     * @return ?mixed Response data
     * @throws ApiAuthenticationException
     */
    public function refresh($token);

    /**
     * @param string $token
     * @return void
     * @throws ApiAuthenticationException
     */
    public function logout($token);

    /**
     * @param string $token
     * @return boolean
     */
    public function isAuthenticated($token);
}
