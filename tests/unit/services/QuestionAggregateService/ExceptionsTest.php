<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use ls\tests\TestBaseClass;

use Mockery;
use Permission;
use Survey;

use LimeSurvey\Models\Services\Exception\{
    PermissionDeniedException
};

/**
 * @group services
 */
class ExceptionsTest extends TestBaseClass
{
    /**
     * @testdox save() throws PermissionDeniedException
     */
    public function testSaveThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(false);

        $mockSet = (new MockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;

        $questionAggregateService = (new Factory)->make( $mockSet);

        $questionAggregateService->save(1, []);
    }

    /**
     * @testdox delete() throws PermissionDeniedException
     */
    public function testDeleteThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(false);
        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('isActive')
            ->andReturn(true);
        $modelSurvey = Mockery::mock(Survey::class)
        ->makePartial();
        $modelSurvey->shouldReceive('findByPk')
            ->andReturn($survey);
        $mockSet = (new MockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;
        $mockSet->modelSurvey = $modelSurvey;

        $questionAggregateService = (new Factory)->make( $mockSet);

        $questionAggregateService->delete(1, 1);
    }
}
