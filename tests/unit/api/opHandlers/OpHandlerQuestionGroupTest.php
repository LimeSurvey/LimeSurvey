<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
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
        $this->initializeWrongTypePatcher();

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    public function testOpQuestionGroupTransformedPropsSimpleCreate()
    {
        $this->initializePatcherCreateSimple();

        $opHandler = $this->getOpHandler();
        $transformedProps = $opHandler->getTransformedProps($this->op);
        self::assertArrayNotHasKey('questionGroup', $transformedProps);
    }

    public function testOpQuestionGroupTransformedPropsCreateWithI10N()
    {
        $this->initializePatcherCreateI10N();

        $opHandler = $this->getOpHandler();
        $transformedProps = $opHandler->getTransformedProps($this->op);
        self::assertArrayHasKey('questionGroupI10N', $transformedProps);
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

    private function initializePatcherCreateSimple()
    {
        $this->op = OpStandard::factory(
            'questionGroup',
            'create',
            [
                'gid' => 1
            ],
            [
                'sid'        => 12345,
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
            [
                'gid' => 1
            ],
            [
                'questionGroup'     => [
                    'sid'                => 12345,
                    'randomizationGroup' => "",
                    'gRelevance'         => ""
                ],
                'questionGroupI10N' => [
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

    private function initializeWrongTypePatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroup',
            'wrongType',
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

        return new OpHandlerQuestionGroup(
            'questionGroup',
            $mockSet->modelQuestionGroup,
            new TransformerInputQuestionGroup(),
            new TransformerInputQuestionGroupL10ns()
        );
    }
}
