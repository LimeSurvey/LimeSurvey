<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use ls\tests\TestBaseClass;

use Mockery;
use Permission;

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

        $mockSet = (new MockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;

        $questionAggregateService = (new Factory)->make( $mockSet);

        $questionAggregateService->delete(1, 1);
    }
}
