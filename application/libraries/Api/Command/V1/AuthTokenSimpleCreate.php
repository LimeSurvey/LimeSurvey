<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};

class AuthTokenSimpleCreate implements CommandInterface
{
    protected AuthenticationTokenSimple $auth;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthenticationTokenSimple $auth
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        AuthenticationTokenSimple $auth,
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
