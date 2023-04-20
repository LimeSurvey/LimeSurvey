<?php

namespace LimeSurvey\SurveyPatch\Op;

class Op implements OpInterface
{
    protected $id = '';

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getId();
    }
}
