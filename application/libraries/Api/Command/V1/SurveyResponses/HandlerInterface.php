<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

interface HandlerInterface
{
    public function canHandle(string $operation): bool;
    public function execute(string $key, string $value): object;
}
