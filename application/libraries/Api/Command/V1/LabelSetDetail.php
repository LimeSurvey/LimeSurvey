<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\LabelSetService;

/**
 * Get one label set with its labels and their translations.
 */
class LabelSetDetail implements CommandInterface
{
    public const ERROR_LABEL_SET_NOT_FOUND = 'LABEL_SET_NOT_FOUND';

    protected ResponseFactory $responseFactory;
    protected LabelSetService $labelSetService;

    /**
     * Create the command with its dependencies.
     *
     * @param ResponseFactory $responseFactory Factory for the API responses
     * @param LabelSetService $labelSetService Service that reads the label sets
     */
    public function __construct(
        ResponseFactory $responseFactory,
        LabelSetService $labelSetService
    ) {
        $this->responseFactory = $responseFactory;
        $this->labelSetService = $labelSetService;
    }

    /**
     * Return the label set of the route id with its labels.
     *
     * @param Request $request The API request
     * @return Response The label set, or a "not found" error
     */
    public function run(Request $request): Response
    {
        try {
            $labelSet = $this->labelSetService->getLabelSet((int)$request->getData('_id'));
        } catch (NotFoundException $e) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(self::ERROR_LABEL_SET_NOT_FOUND, $e->getMessage()))->toArray()
            );
        }

        return $this->responseFactory->makeSuccess(['labelSet' => $labelSet]);
    }
}
