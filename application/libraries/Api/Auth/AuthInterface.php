<?php

namespace LimeSurvey\Api\Auth;

use LimeSurvey\Api\Auth\ApiAuthException;

interface AuthInterface
{
    /**
     * Login with username and password
     *
     * @param $username
     * @param $password
     * @return ?mixed Response data
     * @throws ApiAuthException
     */
    public function login($username, $password);

    /**
     * @param string $token
     * @return void
     * @throws ApiAuthException
     */
    public function logout($token);

    /**
     * @param string $token
     * @return boolean
     */
    public function isAuthenticated($token);

}
