<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

/**
 * A condition handler that cannot build its SQL from the key and value alone.
 */
interface SurveyContextAwareInterface
{
    public function setSurveyId(?int $surveyId): void;
}
