<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\ApiConfig;
use Yii;

/**
 * RestConfig
 *
 */
class RestConfig extends ApiConfig
{
    public function __construct()
    {
        $yiiRestConfig = Yii::app()->getConfig('rest');
        $this->setConfig($yiiRestConfig);
    }
}
