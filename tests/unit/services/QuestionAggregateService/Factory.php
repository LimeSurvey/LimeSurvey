<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use LimeSurvey\Models\Services\QuestionAggregateService;

/**
 * Question Editor Factory
 */
class Factory
{
    /**
     * @param ?MockSet $init
     */
    public function make(MockSet $mockSet = null): QuestionAggregateService
    {
        $mockSet = (new MockSetFactory())->make($mockSet);

        return new QuestionAggregateService(
            $mockSet->saveService,
            $mockSet->deleteService,
            $mockSet->modelPermission,
            $mockSet->modelSurvey,
            $mockSet->yiiDb
        );
    }
}
