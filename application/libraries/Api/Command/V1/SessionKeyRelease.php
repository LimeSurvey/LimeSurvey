<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthTokenSimple;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class SessionKeyRelease implements CommandInterface
{
    protected AuthTokenSimple $authTokenSimple;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthTokenSimple $authTokenSimple
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        AuthTokenSimple $authTokenSimple,
        ResponseFactory $responseFactory
    ) {
        $this->authTokenSimple = $authTokenSimple;
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
        $this->authTokenSimple->doLogout(
            $request
            ->getData('sessionKey')
        );
        return $this->responseFactory
            ->makeSuccess('OK');
    }
}
