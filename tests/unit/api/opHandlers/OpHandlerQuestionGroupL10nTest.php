<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupL10n;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

class OpHandlerQuestionGroupL10nTest extends TestBaseClass
{
    protected OpInterface $op;

    public function testOpQuestionGroupL10nThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializeWrongPropsPatcher();
        $opHandler = $this->getOpHandler();
        $opHandler->getDataArray($this->op);
    }

    public function testOpQuestionGroupL10nThrowsMissingLanguageException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializeMissingLanguagePatcher();
        $opHandler = $this->getOpHandler();
        $opHandler->getDataArray($this->op);
    }

    public function testOpQuestionGroupL10nCanHandle()
    {
        $this->initializePatcher();

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    public function testOpQuestionGroupL10nCanNotHandle()
    {
        $this->initializeWrongEntityTypePatcher();

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    public function testOpQuestionGroupL10nDataStructure()
    {
        $this->initializePatcher();

        $opHandler = $this->getOpHandler();
        $transformedDataArray = $opHandler->getDataArray($this->op);
        self::assertArrayHasKey('en', $transformedDataArray);
    }

    private function initializePatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroupL10n',
            'update',
            [
                'gid'      => 1,
                'language' => 'en'
            ],
            [
                'groupName'   => 'Name of group',
                'description' => 'Description of group'
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function initializeWrongEntityTypePatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroupL10n',
            'create',
            [
                'gid'      => 1,
                'language' => 'en'
            ],
            [
                'unknownA' => '2020-01-01 00:00',
                'unknownB' => true,
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function initializeMissingLanguagePatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroupL10n',
            'update',
            [
                'gid' => 1,
            ],
            [
                'groupName'   => 'Name of group',
                'description' => 'Description of group'
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function initializeWrongPropsPatcher()
    {
        $this->op = OpStandard::factory(
            'questionGroupL10n',
            'update',
            [
                'gid'      => 1,
                'language' => 'en'
            ],
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
