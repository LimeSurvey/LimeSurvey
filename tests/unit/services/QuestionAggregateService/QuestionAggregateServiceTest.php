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
class QuestionAggregateServiceTest extends TestBaseClass
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

        $questionEditor = (new Factory)->make($mockSet);

        $questionEditor->save(1, []);
    }
}
