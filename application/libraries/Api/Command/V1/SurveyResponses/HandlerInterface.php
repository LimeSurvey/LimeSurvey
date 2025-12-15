<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

interface HandlerInterface
{
    public function canHandle(string $operation): bool;

    /**
     * Builds criteria for either one or multiple keys.
     * @param string|array $key
     * @param string|array $value
     * @return \CDbCriteria|\CSort
     */
    public function execute($key, $value): object;
}
