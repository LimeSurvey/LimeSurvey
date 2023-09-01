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

    /**
     * @testdox createGroup() throws PermissionDeniedException
     */
    public function testCreateThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );
        $questionGroupService = $this->getMockedServiceForPermissionDeniedException();
        $questionGroupService->createGroup(1, []);
    }

    /**
     * @testdox deleteGroup() throws PermissionDeniedException
     */
    public function testDeleteThrowsExceptionPermissionDenied()
    {
        $this->expectException(
            PermissionDeniedException::class
        );
        $questionGroupService = $this->getMockedServiceForPermissionDeniedException();
        $questionGroupService->deleteGroup(1, 1);
    }

    /**
     * @testdox updateGroup() throws NotFoundException
     */
    public function testUpdateThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupNotFoundException();
        $questionGroupService->updateGroup(1, 1, []);
    }

    /**
     * @testdox getQuestionGroupObject() throws NotFoundException
     */
    public function testGetQuestionGroupObjectThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupNotFoundException();
        $questionGroupService->getQuestionGroupObject(1, 1);
    }

    /**
     * @testdox createGroup() throws NotFoundException
     */
    public function testCreateThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $questionGroupService = $this->getMockedServiceForSurveyNotFoundException();
        $questionGroupService->createGroup(1, []);
    }

    /**
     * @testdox updateGroup() throws PersistErrorException
     */
    public function testUpdateThrowsExceptionPersistError()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupPersistError();
        $questionGroupService->updateGroup(
            1,
            1,
            ['questionGroup' => []]
        );
    }

    /**
     * @testdox createGroup() throws PersistErrorException
     */
    public function testCreateThrowsExceptionPersistError()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $questionGroupService = $this->getMockedServiceForQuestionGroupPersistError();
        $questionGroupService->createGroup(1, ['questionGroup' => []]);
    }

    /**
     * Returns QuestionGroupService with mocked data especially for permission denied exception
     * @return QuestionGroupService
     */
    private function getMockedServiceForPermissionDeniedException(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelPermission = $mockSetFactory->getMockModelNoSurveyPermission();

        return (new QuestionGroupFactory())->make($mockSet);
    }

    /**
     * Returns QuestionGroupService with mocked data especially for survey not found exception
     * @return QuestionGroupService
     */
    private function getMockedServiceForSurveyNotFoundException(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelSurvey = $mockSetFactory->getMockModelForSurveyNotFound();

        return (new QuestionGroupFactory())->make($mockSet);
    }

    /**
     * Returns QuestionGroupService with mocked data especially for question group not found exception
     * @return QuestionGroupService
     */
    private function getMockedServiceForQuestionGroupNotFoundException(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelQuestionGroup = $mockSetFactory->getMockModelForQuestionGroupNotFound();

        return (new QuestionGroupFactory())->make($mockSet);
    }

    /**
     * Returns QuestionGroupService with mocked data especially for question group persist error
     * @return QuestionGroupService
     */
    private function getMockedServiceForQuestionGroupPersistError(): QuestionGroupService
    {
        $mockSetFactory = new QuestionGroupMockSetFactory();

        $mockSet = $mockSetFactory->make();
        $mockSet->modelQuestionGroup = $mockSetFactory->getMockModelForQuestionGroupPersistError();

        return (new QuestionGroupFactory())->make($mockSet);
    }

}