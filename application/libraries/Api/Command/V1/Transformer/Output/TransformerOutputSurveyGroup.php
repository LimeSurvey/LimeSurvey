<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use SurveysGroups;
use LimeSurvey\Api\Transformer\{
    Output\TransformerOutputActiveRecord,
};

class TransformerOutputSurveyGroup extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'gsid' => ['type' => 'int'],
            'name' => true,
            'title' => true,
            'description' => true,
            'sortorder' => ['key' => 'sortOrder', 'type' => 'int'],
            'alwaysavailable' => ['key' => 'alwaysAvailable', 'type' => 'bool'],
        ]);
    }

    public function transform($data)
    {
        $survey_group = null;
        if (!$data instanceof SurveysGroups) {
            return null;
        }
        $survey_group = parent::transform($data);
        return $survey_group;
    }
}
