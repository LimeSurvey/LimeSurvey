<?php

namespace ls\tests\unit\services\QuestionAggregateService\Save;

use LimeSurvey\Models\Services\QuestionAggregateService\SaveService;

/**
 * Save Factory
 */
class SaveFactory
{
    /**
     * @param ?QuestionMockSet $init
     */
    public function make(SaveMockSet $mockSet = null): SaveService
    {
        $mockSet = (new SaveMockSetFactory())->make($mockSet);

        return new SaveService(
            $mockSet->questionService,
            $mockSet->l10nService,
            $mockSet->attributesService,
            $mockSet->answersService,
            $mockSet->subQuestionsService,
            $mockSet->proxyExpressionManager
        );
    }
}
