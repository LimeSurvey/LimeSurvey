<?php

namespace ls\tests\unit\services\QuestionEditorService;

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
    public function testThrowsExceptionPermissionDenied()
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

        $questionEditor = (new Factory)->make( $mockSet);

        $questionEditor->save([]);
    }
}
