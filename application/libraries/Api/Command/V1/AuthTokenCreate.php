<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Auth\CommandAuthInterface,
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};

class AuthTokenCreate implements CommandInterface
{
    protected CommandAuthInterface $commandAuth;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param CommandAuthInterface $commandAuth
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
     * Run session key create command.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            return $this->responseFactory->makeSuccess(
                $this->commandAuth->login($request)
            );
        } catch (ExceptionInvalidUser $e) {
            return $this->responseFactory->makeErrorUnauthorised(
                (new ResponseDataError(
                    'INVALID_USER',
                    $e->getMessage()
                ))->toArray()
            );
        }
    }
}
