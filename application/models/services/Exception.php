<?php

namespace LimeSurvey\Models\Services;

use LSActiveRecord;

class Exception extends \Exception
{
    private $errorModel = null;

    public function setErrorModel(LSActiveRecord $errorModel)
    {
        $this->errorModel = $errorModel;
    }

    public function getErrorModel()
    {
        return $this->errorModel;
    }
}
