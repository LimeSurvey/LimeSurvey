<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Models\Services\LabelSetService;

/**
 * List label sets.
 *
 * Query params:
 * - languages: Comma separated language codes, only sets containing all of them are listed
 * - updatable: '1' to only list sets the current user may overwrite
 */
class LabelSetList implements CommandInterface
{
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
     * Return the label sets matching the "languages" and "updatable" query params.
     *
     * @param Request $request The API request
     * @return Response The label set list
     */
    public function run(Request $request): Response
    {
        $languages = $request->getData('languages') ?? [];
        $onlyUpdatable = in_array($request->getData('updatable'), ['1', 'true', 1, true], true);

        return $this->responseFactory->makeSuccess([
            'labelSets' => $this->labelSetService->getLabelSets(
                is_array($languages) ? $languages : explode(',', (string)$languages),
                $onlyUpdatable
            ),
        ]);
    }
}
