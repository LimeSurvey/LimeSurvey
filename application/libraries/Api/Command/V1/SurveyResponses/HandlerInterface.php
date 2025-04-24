<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

interface HandlerInterface
{
    public function canHandle(array $operation): bool;
    public function execute(string $key, array $value): object;
}
