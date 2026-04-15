<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class AuthTokenSimpleRefresh implements CommandInterface
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
     * Run refresh token command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $authToken = (string) $request->getData('authToken');

        try {
            return $this->responseFactory->makeSuccess(
                $this->auth->refresh(
                    $authToken
                )
            );
        } catch (ExceptionInvalidUser $e) {
            return $this->responseFactory->makeErrorUnauthorised();
        }
    }
}
