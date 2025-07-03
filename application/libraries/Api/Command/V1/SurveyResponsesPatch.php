<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use DI\FactoryInterface;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\PatcherSurveyResponses;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory};

class SurveyResponsesPatch implements CommandInterface
{
    protected FactoryInterface $diFactory;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    ) {
        $this->diFactory = $diFactory;
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
        $id = (string) $request->getData('_id');
        $patch = $request->getData('patch');

        $patcher = $this->diFactory->make(
            PatcherSurveyResponses::class
        );

        try {
            $returnedData = $patcher->applyPatch($patch, ['id' => $id]);
        } catch (ObjectPatchException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory
            ->makeSuccess($returnedData);
    }
}
