<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Auth\CommandAuthInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class AuthTokenRelease implements CommandInterface
{
    protected CommandAuthInterface $commandAuth;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        CommandAuthInterface $commandAuth,
        ResponseFactory $responseFactory
    ) {
        $this->commandAuth = $commandAuth;
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
        $this->commandAuth->logout($request);
        return $this->responseFactory
            ->makeSuccess('OK');
    }
}
