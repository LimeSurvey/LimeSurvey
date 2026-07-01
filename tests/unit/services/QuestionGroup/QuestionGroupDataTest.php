<?php

namespace ls\tests\unit\services\QuestionGroup;

use ls\tests\TestBaseClass;

class QuestionGroupDataTest extends TestBaseClass
{
    /**
     * @testdox getGroupData() check for search parameters
     */
    public function testGetGroupData()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $mockSet->survey->setAttributes([
            'sid'      => 12345,
            'language' => 'en'
        ]);
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);
        $returnedGroup = $questionGroupService->getGroupData(
            $mockSet->survey,
            ['group_name' => 'group1']
        );

        $this->assertEquals(
            'group1',
            $returnedGroup->group_name
        );
        $this->assertEquals(
            12345,
            $returnedGroup->sid
        );
        $this->assertEquals(
            'en',
            $returnedGroup->language
        );
    }

    /**
     * @testdox updateQuestionGroupLanguages() check valid save
     */
    public function testUpdateQuestionGroupLanguages()
    {
        $dataSet = [
            'en' =>
                [
                    'group_name'  => 'Group title_EN',
                    'description' => 'eng'
                ],
            'fr' =>
                [
                    'group_name'  => 'Group title_FR',
                    'description' => 'fr'
                ]
        ];
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);
        $returnedBoolean = $questionGroupService->updateQuestionGroupLanguages(
            $mockSet->questionGroup,
            $dataSet
        );

        $this->assertTrue($returnedBoolean);
    }

    /**
     * @testdox newQuestionGroup() check valid save
     */
    public function testNewQuestionGroup()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);
        $mockSet->survey->setAttributes(['sid' => 123456]);
        $returnedGroup = $questionGroupService->newQuestionGroup(
            123456,
            ['gid' => 12]
        );

        $this->assertEquals(123456, $returnedGroup->sid);
        $this->assertEquals(1, $returnedGroup->group_order);
    }

    /**
     * @testdox  updateQuestionGroup() check if it returns instance
     */
    public function testUpdateQuestionGroup()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);
        $returnedGroup = $questionGroupService->updateQuestionGroup(
            $mockSet->questionGroup,
            []
        );

        $this->assertInstanceOf('QuestionGroup', $returnedGroup);
    }

    /**
     * @testdox  reorderQuestionGroups() check when nothing is changed
     */
    public function testReorderQuestionGroups()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $mockSet->survey->setAttributes(['active' => 'N']);
        $questionGroupService = (new QuestionGroupFactory())->make($mockSet);
        $returnedArray = $questionGroupService->reorderQuestionGroups(
            123456,
            []
        );

        $this->assertNotEquals('', $returnedArray['message']);
        $this->assertEquals(true, $returnedArray['success']);
    }
}