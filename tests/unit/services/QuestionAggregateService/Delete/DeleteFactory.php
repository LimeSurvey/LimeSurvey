<?php

namespace ls\tests\unit\services\QuestionAggregateService\Delete;

use LimeSurvey\Models\Services\QuestionAggregateService\DeleteService;

/**
 * Delete Factory
 */
class DeleteFactory
{
    /**
     * @param ?DeleteMockSet $init
     */
    public function make(DeleteMockSet $mockSet = null): DeleteService
    {
        $mockSet = (new DeleteMockSetFactory())->make($mockSet);

        return new DeleteService(
            $mockSet->modelQuestion,
            $mockSet->modelQuestionL10n,
            $mockSet->modelCondition,
            $mockSet->proxyExpressionManager
        );
    }
}
