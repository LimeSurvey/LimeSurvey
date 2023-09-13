<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupL10n;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    Op\OpStandard,
    OpHandler\OpHandlerException
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
     * @testdox getTransformedLanguageProps throws no values exception
     */
    public function testOpQuestionGroupL10nThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getWrongProps()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->getTransformedLanguageProps(
            $this->op,
            new TransformerInputQuestionGroupL10ns(),
            'questionGroupL10n'
        );
    }

    /**
     * @testdox getTransformedLanguageProps throws missing language exception
     */
    public function testOpQuestionGroupL10nThrowsMissingLanguageException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getMissingLanguageProps()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->getTransformedLanguageProps(
            $this->op,
            new TransformerInputQuestionGroupL10ns(),
            'questionGroupL10n'
        );
    }

    /**
     * @testdox can handle a questionGroupL10n update
     */
    public function testOpQuestionGroupL10nCanHandle()
    {
        $this->initializePatcher(
            $this->getDefaultProps()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can not handle a questionGroupL10n create
     */
    public function testOpQuestionGroupL10nCanNotHandle()
    {
        $this->initializePatcher(
            $this->getDefaultProps(),
            'create'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @testdox getTransformedLanguageProps returns the expected array
     */
    public function testOpQuestionGroupL10nDataStructure()
    {
        $this->initializePatcher(
            $this->getDefaultProps()
        );

        $opHandler = $this->getOpHandler();
        $transformedDataArray = $opHandler->getTransformedLanguageProps(
            $this->op,
            new TransformerInputQuestionGroupL10ns(),
            'questionGroupL10n'
        );
        self::assertArrayHasKey('en', $transformedDataArray);
    }

    private function initializePatcher(
        array $propsArray,
        string $type = 'update'
    ) {
        $this->op = OpStandard::factory(
            'questionGroupL10n',
            $type,
            123,
            $propsArray,
            [
                'id' => 123456
            ]
        );
    }

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
            new TransformerInputQuestionGroupL10ns()
        );
    }
}
