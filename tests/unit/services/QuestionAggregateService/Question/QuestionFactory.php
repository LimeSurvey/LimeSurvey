<?php

namespace ls\tests\unit\services\QuestionAggregateService\Question;

use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;

/**
 * Question Factory
 */
class QuestionFactory
{
    /**
     * @param ?QuestionMockSet $init
     */
    public function make(QuestionMockSet $mockSet = null): QuestionService
    {
        $mockSet = (new QuestionMockSetFactory())->make($mockSet);

        return new QuestionService(
            $mockSet->modelQuestion,
            $mockSet->modelSurvey,
            $mockSet->modelCondition,
            $mockSet->l10nService,
            $mockSet->proxySettingsUser,
            $mockSet->proxyQuestion,
            $mockSet->yiiApp,
        );
    }
}
