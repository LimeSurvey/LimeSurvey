<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

/**
 * VersionInfo
 *
 * Public endpoint just to retrieve info on the current dbVersion and assetsVersionNumber
 */
class VersionInfo implements CommandInterface
{
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run settings command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        return $this->responseFactory
            ->makeSuccess([
                'dbVersion' => App()->getConfig('dbversionnumber'),
                'assetsVersionNumber' => App()->getConfig('assetsversionnumber'),
            ]);
    }
}
