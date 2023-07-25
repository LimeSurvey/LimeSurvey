<?php

namespace ls\tests\unit\services\QuestionGroup;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\QuestionGroupService;
use ls\tests\TestBaseClass;
use Mockery;
use Permission;
use QuestionGroup;

class QuestionGroupExceptionsTest extends TestBaseClass
{
    /**
     * @testdox updateGroup() throws PermissionDeniedException
     */
    public function testUpdateThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );
        $questionGroupService = $this->getMockedServiceForPermissionDeniedException();
        $questionGroupService->updateGroup(1, 1, []);
    }

    public function testCreateThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );
        $questionGroupService = $this->getMockedServiceForPermissionDeniedException();
        $questionGroupService->createGroup(1, []);
    }

    public function testDeleteThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );
        $questionGroupService = $this->getMockedServiceForPermissionDeniedException();
        $questionGroupService->deleteGroup(1, 1);
    }

    public function testUpdateThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupNotFoundException();
        $questionGroupService->updateGroup(1, 1, []);
    }

    public function testGetQuestionGroupObjectThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupNotFoundException();
        $questionGroupService->getQuestionGroupObject(1, 1);
    }

    public function testCreateThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $questionGroupService = $this->getMockedServiceForSurveyNotFoundException();
        $questionGroupService->createGroup(1, []);
    }

    public function testUpdateThrowsExceptionPersistError()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupPersistError();
        $questionGroupService->updateGroup(1, 1, ['questionGroup' => []]);
    }

    public function testCreateThrowsExceptionPersistError()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupPersistError();
        $questionGroupService->createGroup(1, ['questionGroup' => []]);
    }

    private function getMockedServiceForPermissionDeniedException(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelPermission = $mockSetFactory->getMockModelNoSurveyPermission();

        return (new QuestionGroupFactory())->make($mockSet);
    }

    private function getMockedServiceForSurveyNotFoundException(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelSurvey = $mockSetFactory->getMockModelForSurveyNotFound();

        return (new QuestionGroupFactory())->make($mockSet);
    }

    private function getMockedServiceForQuestionGroupNotFoundException(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelQuestionGroup = $mockSetFactory->getMockModelForQuestionGroupNotFound();

        return (new QuestionGroupFactory())->make($mockSet);
    }

    private function getMockedServiceForQuestionGroupPersistError(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelQuestionGroup = $mockSetFactory->getMockModelForQuestionGroupPersistError();

        return (new QuestionGroupFactory())->make($mockSet);
    }

}