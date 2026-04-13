<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Permission;
use Survey;
use CDbConnection;
use Mockery;
use ls\tests\unit\services\QuestionAggregateService\{
    Save\SaveFactory,
    Delete\DeleteFactory
};

/**
 * Question Aggregate Mock Factory
 *
 * Reusable initialisation of mock dependencies for use in QuestionEditor tests.
 */
class MockSetFactory
{
    /**
     * @param ?MockSet $init
     */
    public function make(MockSet $init = null): MockSet
    {
        $mockSet = new MockSet;

        $mockSet->saveService = ($init && isset($init->saveService))
            ? $init->saveService
            : (new SaveFactory)->make();

        $mockSet->deleteService = ($init && isset($init->deleteService))
            ? $init->deleteService
            : (new DeleteFactory)->make();

        $mockSet->modelPermission = ($init && isset($init->modelPermission))
            ? $init->modelPermission
            : $this->getMockModelPermission();

        $mockSet->modelSurvey = ($init && isset($init->modelSurvey))
            ? $init->modelSurvey
            : $this->getMockModelSurvey();

        $mockSet->yiiDb = ($init && isset($init->yiiDb))
            ? $init->yiiDb
            : $this->getMockYiiDb();

        return $mockSet;
    }

    private function getMockModelPermission(): Permission
    {
        return Mockery::mock(Permission::class)
            ->makePartial();
    }

    private function getMockModelSurvey(): Survey
    {
        return Mockery::mock(Survey::class)
            ->makePartial();
    }

    private function getMockYiiDb(): CDbConnection
    {
        return Mockery::mock(CDbConnection::class)
            ->makePartial();
    }
}
