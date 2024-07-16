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
                $this->auth->login(
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
