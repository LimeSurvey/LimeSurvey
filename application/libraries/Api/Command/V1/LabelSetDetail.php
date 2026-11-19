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
        // The route accepts any path segment, and a cast would turn "12abc" into set 12
        $lid = filter_var($request->getData('_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($lid === false) {
            return $this->makeNotFound('Label set not found');
        }

        try {
            $labelSet = $this->labelSetService->getLabelSet($lid);
        } catch (NotFoundException $e) {
            return $this->makeNotFound($e->getMessage());
        }

        return $this->responseFactory->makeSuccess(['labelSet' => $labelSet]);
    }

    /**
     * Make a "not found" response for a label set.
     *
     * @param string $message Why the label set wasn't found
     * @return Response The error response
     */
    private function makeNotFound(string $message): Response
    {
        return $this->responseFactory->makeErrorNotFound(
            (new ResponseDataError(self::ERROR_LABEL_SET_NOT_FOUND, $message))->toArray()
        );
    }
}
