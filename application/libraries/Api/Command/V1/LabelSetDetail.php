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

    public function __construct(
        ResponseFactory $responseFactory,
        LabelSetService $labelSetService
    ) {
        $this->responseFactory = $responseFactory;
        $this->labelSetService = $labelSetService;
    }

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
