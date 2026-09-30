<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

/**
 * A condition handler that cannot build its SQL from the key and value alone.
 *
 * Most handlers are survey-agnostic: a column name and a value are enough. A
 * handler that has to know how a column is *stored* needs the response table
 * too, and that table is per survey.
 */
interface SurveyContextAwareInterface
{
    public function setSurveyId(?int $surveyId): void;
}
