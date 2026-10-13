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
use QuestionGroupL10n;

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
     * @testdox updateQuestionGroupLanguagesOrFail() saves all languages and throws PersistErrorException with error model
     */
    public function testUpdateLanguagesThrowsExceptionPersistErrorWithErrorModel()
    {
        $failingL10n = Mockery::mock(QuestionGroupL10n::class)->makePartial();
        $failingL10n->shouldReceive('save')->once()->andReturn(false);
        $failingL10n->addError('description', 'Links are not allowed.');
        $validL10n = Mockery::mock(QuestionGroupL10n::class)->makePartial();
        $validL10n->shouldReceive('save')->once()->andReturn(true);

        $mockSetFactory = new QuestionGroupMockSetFactory();
        $mockSet = $mockSetFactory->make();
        $mockSet->modelQuestionGroupL10n = Mockery::mock(QuestionGroupL10n::class)->makePartial();
        $mockSet->modelQuestionGroupL10n->shouldReceive('findByAttributes')
            ->andReturn($failingL10n, $validL10n);
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);

        try {
            $questionGroupService->updateQuestionGroupLanguagesOrFail(
                $mockSet->questionGroup,
                [
                    'en' => ['description' => 'https://example.com'],
                    'de' => ['description' => 'Beschreibung'],
                ]
            );
            $this->fail('PersistErrorException was not thrown');
        } catch (PersistErrorException $e) {
            $this->assertStringContainsString('(en)', $e->getMessage());
            $this->assertSame($failingL10n, $e->getErrorModel());
        }
    }

    /**
     * @testdox updateQuestionGroupLanguages() returns false instead of throwing
     */
    public function testUpdateLanguagesReturnsFalseOnPersistError()
    {
        $failingL10n = Mockery::mock(QuestionGroupL10n::class)->makePartial();
        $failingL10n->shouldReceive('save')->andReturn(false);

        $mockSetFactory = new QuestionGroupMockSetFactory();
        $mockSet = $mockSetFactory->make();
        $mockSet->modelQuestionGroupL10n = Mockery::mock(QuestionGroupL10n::class)->makePartial();
        $mockSet->modelQuestionGroupL10n->shouldReceive('findByAttributes')
            ->andReturn($failingL10n);
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);

        $this->assertFalse(
            $questionGroupService->updateQuestionGroupLanguages(
                $mockSet->questionGroup,
                ['en' => ['description' => 'https://example.com']]
            )
        );
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