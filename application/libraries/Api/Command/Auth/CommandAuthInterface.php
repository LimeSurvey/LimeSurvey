<?php

namespace LimeSurvey\Api\Command\Auth;

use LimeSurvey\Api\Command\{
    Request\Request
};

interface CommandAuthInterface
{
    /**
     * @param Request $request
     * @return ?mixed
     */
    public function login($request);

    /**
     * @param Request $request
     * @return ?mixed
     */
    public function logout($request);

    /**
     * @param Request $request
     * @return boolean
     */
    public function isAuthenticated($request);
}
