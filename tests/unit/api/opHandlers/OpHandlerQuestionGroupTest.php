<?php

namespace ls\tests\unit\api\opHandlers;

use Mockery;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupAggregate;
use LimeSurvey\ObjectPatch\{
    Op\OpStandard,
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox OpHandlerQuestionGroupTest
 */
class OpHandlerQuestionGroupTest extends TestBaseClass
{
    /**
     * @testdox can handle
     */
    public function testOpQuestionGroupCanHandle()
    {
        $op = $this->getOp($this->getPropsValid());
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox can not handle
     */
    public function testOpQuestionGroupCanNotHandle()
    {
        $op = $this->getOp(
            $this->getPropsValid(),
            'update',
            'not-questionGroup'
        );
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox validation for update hits
     */
    public function testOpQuestionGroupValidationFailure()
    {
        $op = $this->getOp(
            $this->getUpdatePropsInvalid()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);

        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation for update doesn't hit when everything is fine
     */
    public function testOpQuestionGroupValidationSuccess()
    {
        $op = $this->getOp(
            $this->getPropsValid()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @testdox validation for create hits
     */
    public function testOpQuestionGroupCreateValidationFailure()
    {
        $op = $this->getOp(
            $this->getPropsValid(),
            'create'
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation for create doesn't hit when everything is fine
     */
    public function testOpQuestionGroupCreateValidationSuccess()
    {
        $op = $this->getOp(
            $this->getPropsValid(true),
            'create'
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @param array $props
     * @param string $type
     * @param string $entity
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\OpHandlerException
     */
    private function getOp(
        $props = [],
        $type = 'update',
        $entity = 'questionGroup'
    ) {
        return OpStandard::factory(
            $entity,
            $type,
            12345,
            $props,
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @param bool $create
     * @return array
     */
    private function getPropsValid($create = false)
    {
        $groupProps = [
            'randomizationGroup' => '1',
            'gRelevevance'       => '1'
        ];
        if ($create) {
            $groupProps['tempId'] = 'X123';
        }
        return [
            'questionGroup'     => $groupProps,
            'questionGroupL10n' => [
                'en' => [
                    'groupName'   => 'questionGroup',
                    'description' => 'questionGroup descr'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getUpdatePropsInvalid()
    {
        return [
            'unknownA' => '2020-01-01 00:00',
            'unknownB' => true,
        ];
    }

    /**
     * @return OpHandlerQuestionGroup
     */
    private function getOpHandler()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        /** @var QuestionGroupService */
        $mockQuestionGroupService = Mockery::mock(QuestionGroupService::class)
            ->makePartial();
        return new OpHandlerQuestionGroup(
            $mockSet->modelQuestionGroup,
            $mockQuestionGroupService,
            DI::getContainer()->get(
                TransformerInputQuestionGroupAggregate::class
            )
        );
    }
}
