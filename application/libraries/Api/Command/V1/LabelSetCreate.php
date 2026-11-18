<?php

namespace LimeSurvey\Api\Command\V1;

use LabelSet;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Models\Services\Exception\BadRequestException;

/**
 * Create a label set (POST v1/label-sets).
 */
class LabelSetCreate extends LabelSetSave
{
    /**
     * Create the label set from the request body.
     *
     * @param Request $request The API request
     * @param string[] $languages Languages to save
     * @param array $labels Labels to save
     * @return LabelSet The saved label set
     * @throws \LimeSurvey\Models\Services\Exception When the label set can't be saved
     */
    protected function save(Request $request, array $languages, array $labels): LabelSet
    {
        $name = $request->getData('name') ?? '';
        if (!is_string($name)) {
            throw new BadRequestException('Invalid label set name');
        }
        return $this->labelSetService->create($name, $languages, $labels);
    }
}
