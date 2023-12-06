<?php

namespace ls\tests\unit\api\opHandlers;

use Mockery;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupAggregate;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

class OpHandlerQuestionGroupTest extends TestBaseClass
{
    protected OpInterface $op;

    public function testOpQuestionGroupThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializeWrongPropsPatcher();
        $opHandler = $this->getOpHandler();
        $opHandler->setOperationTypes($this->op);
        $opHandler->handle($this->op);
    }

    public function testOpQuestionGroupCanHandle()
    {
        $this->initializePatcher();

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    public function testOpQuestionGroupCanNotHandle()
    {
        $this->initializeWrongEntityPatcher();

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    private function initializePatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroup',
            'update',
            12345,
            [
                'groupOrder' => '1000'
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function initializePatcherCreateI10N()
    {
        $this->op = OpStandard::factory(
            'questionGroup',
            'create',
            1,
            [
                'questionGroup'     => [
                    'sid'                => 12345,
                    'randomizationGroup' => "",
                    'gRelevance'         => ""
                ],
                'questionGroupL10n' => [
                    'en' => [
                        'group_name'  => '3rd Group',
                        'description' => 'English'
                    ],
                    'de' => [
                        'group_name'  => 'Dritte Gruppe',
                        'description' => 'Deutsch'
                    ]
                ]
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function initializeWrongEntityPatcher()
    {
        $this->op = OpStandard::factory(
            'survey',
            'update',
            null,
            [
                'unknownA' => '2020-01-01 00:00',
                'unknownB' => true,
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function initializeWrongPropsPatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroup',
            'update',
            12345,
            [
                'unknownA' => '2020-01-01 00:00',
                'unknownB' => true,
            ],
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @return OpHandlerQuestionGroup
     */
    private function getOpHandler()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $mockQuestionGroupService = Mockery::mock(QuestionGroupService::class)
            ->makePartial();

        return new OpHandlerQuestionGroup(
            $mockSet->modelQuestionGroup,
            $mockQuestionGroupService,
            DI::getContainer()->get(TransformerInputQuestionGroupAggregate::class)
        );
    }
}
