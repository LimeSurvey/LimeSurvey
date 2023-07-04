<?php

namespace ls\tests\unit\services\SurveyUpdater\GeneralSettings;

use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionEditor\{
    QuestionEditorMockSetFactory,
    QuestionEditorFactory
};

use Mockery;
use Permission;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

class QuestionEditorExceptionsTest extends TestBaseClass
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

        $mockSet = (new QuestionEditorMockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;

        $questionEditor = (new QuestionEditorFactory)->make( $mockSet);

        $questionEditor->save([]);
    }
}
