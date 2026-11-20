<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    Request\Request,
    Response\Response
};
use LimeSurvey\Models\Services\Exception\NotFoundException;

/**
 * Get one label set with its labels and their translations.
 */
class LabelSetDetail extends LabelSetCommand
{
    /**
     * Return the label set of the route id with its labels.
     *
     * @param Request $request The API request
     * @return Response The label set, or a "not found" error
     */
    public function run(Request $request): Response
    {
        $lid = $this->getRouteLabelSetId($request);
        if ($lid === null) {
            return $this->makeNotFound('Label set not found');
        }

        try {
            $labelSet = $this->labelSetService->getLabelSet($lid);
        } catch (NotFoundException $e) {
            return $this->makeNotFound($e->getMessage());
        }

        return $this->responseFactory->makeSuccess(['labelSet' => $labelSet]);
    }
}
