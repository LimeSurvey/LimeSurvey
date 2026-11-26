<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};
use LimeSurvey\Models\Services\LabelSetService;

/**
 * Dependencies, route id check and "not found" response shared by the label set commands,
 * so the endpoints with an id can't drift apart.
 */
abstract class LabelSetCommand implements CommandInterface
{
    public const ERROR_LABEL_SET_NOT_FOUND = 'LABEL_SET_NOT_FOUND';

    protected ResponseFactory $responseFactory;
    protected LabelSetService $labelSetService;

    /**
     * Create the command with its dependencies.
     *
     * @param ResponseFactory $responseFactory Factory for the API responses
     * @param LabelSetService $labelSetService Service that reads and writes the label sets
     */
    public function __construct(
        ResponseFactory $responseFactory,
        LabelSetService $labelSetService
    ) {
        $this->responseFactory = $responseFactory;
        $this->labelSetService = $labelSetService;
    }

    /**
     * The label set id of the route.
     *
     * The route accepts any path segment, and a cast would turn "12abc" into set 12.
     *
     * @param Request $request The API request
     * @return int|null The id, null when it isn't a positive integer
     */
    protected function getRouteLabelSetId(Request $request): ?int
    {
        $lid = filter_var($request->getData('_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $lid === false ? null : $lid;
    }

    /**
     * Make a "not found" response for a label set.
     *
     * @param string $message Why the label set wasn't found
     * @return Response The error response
     */
    protected function makeNotFound(string $message): Response
    {
        return $this->responseFactory->makeErrorNotFound(
            (new ResponseDataError(self::ERROR_LABEL_SET_NOT_FOUND, $message))->toArray()
        );
    }
}
