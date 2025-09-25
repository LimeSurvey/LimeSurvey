<?php

namespace ls\tests\unit\services\QuestionOrderingService;

use LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService;
use ReflectionClass;

class QuestionOrderingServiceFactory
{
    public function make($mockSet)
    {
        $service = new QuestionOrderingService();

        // Use reflection to inject mocked dependencies
        $reflection = new ReflectionClass($service);

        if (isset($mockSet->sortingStrategy)) {
            $strategyProp = $reflection->getProperty('sortingStrategy');
            $strategyProp->setAccessible(true);
            $strategyProp->setValue($service, $mockSet->sortingStrategy);
        }

        if (isset($mockSet->randomizerHelper)) {
            $randomizerProp = $reflection->getProperty('randomizerHelper');
            $randomizerProp->setAccessible(true);
            $randomizerProp->setValue($service, $mockSet->randomizerHelper);
        }

        return $service;
    }
}
