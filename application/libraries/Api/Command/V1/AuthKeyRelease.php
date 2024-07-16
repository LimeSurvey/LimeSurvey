<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthTokenSimple;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class AuthKeyRelease implements CommandInterface
{
    protected AuthTokenSimple $auth;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthTokenSimple $auth
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        AuthTokenSimple $auth,
        ResponseFactory $responseFactory
    ) {
        $this->auth = $auth;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run session key release command.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $this->auth->logout(
            $request
            ->getData('authToken')
        );
        return $this->responseFactory
            ->makeSuccess('OK');
    }
}
