<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupL10n;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    Op\OpStandard,
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox OpHandlerQuestionGroupL10n
 */
class OpHandlerQuestionGroupL10nTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox can handle a questionGroupL10n update
     */
    public function testOpQuestionGroupL10nCanHandle()
    {
        $op = $this->getOp(
            $this->getDefaultProps()
        );
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox can not handle a questionGroupL10n create
     */
    public function testOpQuestionGroupL10nCanNotHandle()
    {
        $op = $this->getOp(
            $this->getDefaultProps(),
            'create'
        );
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox validation hits if language indexes are missing
     */
    public function testOpQuestionGroupL10nValidationFailure()
    {
        $op = $this->getOp(
            $this->getMissingLanguageProps()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation doesn't hit when everything is fine
     */
    public function testOpQuestionGroupL10nValidationSuccess()
    {
        $op = $this->getOp(
            $this->getDefaultProps()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @param array $props
     * @param string $type
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\OpHandlerException
     */
    private function getOp(array $props, string $type = 'update')
    {
        return OpStandard::factory(
            'questionGroupL10n',
            $type,
            123,
            $props,
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @return array
     */
    private function getDefaultProps()
    {
        return [
            'en' => [
                'groupName'   => 'Name of group',
                'description' => 'Description of group'
            ],
            'de' => [
                'groupName'   => 'Gruppenname',
                'description' => 'Gruppenbeschreibung'
            ]
        ];
    }

    /**
     * @return array
     */
    private function getMissingLanguageProps()
    {
        return [
            [
                'groupName'   => 'Name of group',
                'description' => 'Description of group'
            ],
            [
                'groupName'   => 'Gruppenname',
                'description' => 'Gruppenbeschreibung'
            ]
        ];
    }

    /**
     * @return array
     */
    private function getWrongProps()
    {
        return [
            'en' => [
                'unknownA' => '2020-01-01 00:00',
                'unknownB' => true,
            ]
        ];
    }

    /**
     * @return OpHandlerQuestionGroupL10n
     */
    private function getOpHandler()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        return new OpHandlerQuestionGroupL10n(
            $mockSet->modelQuestionGroupL10n,
            DI::getContainer()->get(TransformerInputQuestionGroupL10ns::class)
        );
    }
}
