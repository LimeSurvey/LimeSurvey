<?php

namespace LimeSurvey\Api\Command\V1;

use LabelSet;
use LimeSurvey\Api\Command\Request\Request;

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
     * @throws \LimeSurvey\Models\Services\Exception When the label set can't be saved
     */
    protected function save(Request $request, array $languages, array $labels): LabelSet
    {
        return $this->labelSetService->overwrite(
            (int)$request->getData('_id'),
            $languages,
            $labels
        );
    }
}
