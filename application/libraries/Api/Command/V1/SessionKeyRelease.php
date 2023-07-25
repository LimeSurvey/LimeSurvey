<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class SessionKeyRelease implements CommandInterface
{
    protected AuthSession $authSession;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        AuthSession $authSession,
        ResponseFactory $responseFactory
    ) {
        $this->authSession = $authSession;
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
        $this->authSession->doLogout(
            $request
            ->getData('sessionKey')
        );
        return $this->responseFactory
            ->makeSuccess('OK');
    }
}
