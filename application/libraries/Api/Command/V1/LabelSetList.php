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
 * - languages: comma separated language codes, only sets containing all of them are listed
 * - updatable: '1' to only list sets the current user may overwrite
 */
class LabelSetList implements CommandInterface
{
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
