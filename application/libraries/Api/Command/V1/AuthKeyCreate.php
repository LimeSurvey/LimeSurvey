<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthTokenSimple;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};

class AuthKeyCreate implements CommandInterface
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
     * Run session key create command.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');

        try {
            return $this->responseFactory->makeSuccess(
                $this->authTokenSimple->login(
                    $username,
                    $password
                )
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
