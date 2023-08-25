<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\Op\OpInterface;

class OpApiHelper
{
    public static function getEntityId(
        OpInterface $op,
        TransformerInterface $transformer
    ) {
        return is_array($op->getEntityId()) && $transformer
            ? $transformer->transform($op->getEntityId())
            : $op->getEntityId();
    }
}
