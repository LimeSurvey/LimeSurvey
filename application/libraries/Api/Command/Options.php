<?php

namespace LimeSurvey\Api\Command;

use LimeSurvey\Api\Command\{
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

class Options implements CommandInterface
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
     * Run survey patch command
     *
     * Apply patch and respond with update patch to be applied to the source (if any).
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        return $this->responseFactory->makeSuccessNoContent();
    }
}
