<?php

namespace LimeSurvey\SurveyPatch\Op;

class OpUpdate extends Op
{
    public function __construct()
    {
        $this->id = 'UPDATE';
    }
}
