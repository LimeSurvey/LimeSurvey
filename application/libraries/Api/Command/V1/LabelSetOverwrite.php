<?php

namespace LimeSurvey\Api\Command\V1;

use LabelSet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Models\Services\Exception\BadRequestException;

/**
 * Replace the languages and all labels of a label set (PUT v1/label-sets/$id).
 * Like the classic editor, translations of languages that are not sent are removed.
 */
class LabelSetOverwrite extends LabelSetSave
{
    /**
     * Overwrite the label set of the route id with the request body.
     *
     * @param Request $request The API request
     * @param string[] $languages Languages to save
     * @param array $labels Labels to save
     * @return LabelSet The saved label set
     * @throws BadRequestException When the route id is not a positive integer
     * @throws \LimeSurvey\Models\Services\Exception When the label set can't be saved
     */
    protected function save(Request $request, array $languages, array $labels): LabelSet
    {
        // The route accepts any path segment, and a cast would turn "12abc" into set 12
        $lid = filter_var($request->getData('_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($lid === false) {
            throw new BadRequestException('Invalid label set id');
        }
        return $this->labelSetService->overwrite($lid, $languages, $labels);
    }
}
