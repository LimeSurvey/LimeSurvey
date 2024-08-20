<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class AuthTokenSimpleRelease implements CommandInterface
{
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
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
        return $this->responseFactory
            ->makeSuccess('OK');
    }
}
