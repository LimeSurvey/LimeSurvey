<?php

namespace LimeSurvey\JsonPatch\Patcher;

use LimeSurvey\JsonPatch\Patcher;
use LimeSurvey\JsonPatch\OpHandler\OpHandlerQuestionGroupPropReplace;
use LimeSurvey\JsonPatch\OpHandler\OpHandlerSurveyPropReplace;

class PatcherSurvey extends Patcher
{
    public function __construct($surveyId)
    {
        $this->setParams([
            'surveyId' => $surveyId
        ]);
        $this->addOpHandler(new OpHandlerSurveyPropReplace);
        $this->addOpHandler(new OpHandlerQuestionGroupPropReplace);
    }
}
