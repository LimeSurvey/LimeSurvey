<?php

namespace LimeSurvey\JsonPatch\Patcher;

use LimeSurvey\JsonPatch\Patcher;
use LimeSurvey\JsonPatch\OpHandler\OpHandlerQuestionGroupProp;
use LimeSurvey\JsonPatch\OpHandler\OpHandlerSurveyPropUpdate;

class PatcherSurvey extends Patcher
{
    public function __construct($surveyId)
    {
        $this->setParams([
            'surveyId' => $surveyId
        ]);
        $this->addOpHandler(new OpHandlerSurveyPropUpdate);
        $this->addOpHandler(new OpHandlerQuestionGroupProp);
    }
}
