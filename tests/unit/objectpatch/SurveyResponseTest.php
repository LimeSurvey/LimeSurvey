<?php

namespace ls\tests\unit\objectpatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionCreate;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\SurveyResponse;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapItem;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapping;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerValidationTrait;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox SurveyResponse
 */
class SurveyResponseTest extends TestBaseClass
{
    use OpHandlerValidationTrait;

    /** @var  array */
    protected static $responseObject;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$responseObject = self::getResponse();
    }

    /**
     * @testdox response contains mapping of tempId to real Id
     */
    public function testContentOfResponse()
    {
        $responseObject = self::$responseObject;
        $this->assertIsArray($responseObject);
        $this->assertArrayHasKey('tempIdMapping', $responseObject);
        $this->assertNotEmpty($responseObject['tempIdMapping']);
        $tempIdMapping = $responseObject['tempIdMapping'];
        $this->assertIsArray($tempIdMapping);
        $this->assertArrayHasKey('questionsMap', $tempIdMapping);
        $this->assertArrayHasKey('subquestionsMap', $tempIdMapping);
        $subQuestionsMap = $tempIdMapping['subquestionsMap'][0];
        $this->assertIsObject($subQuestionsMap);
        $this->assertEquals('tmp789', $subQuestionsMap->tempId);
        $this->assertEquals('1025', $subQuestionsMap->id);
    }

    /**
     * @testdox response contains validation errors
     */
    public function testValidationErrors()
    {
        $responseObject = self::$responseObject;
        $this->assertIsArray($responseObject);
        $this->assertArrayHasKey('validationErrors', $responseObject);
        $this->assertNotEmpty($responseObject['validationErrors']);
        $validationErrorItem = $responseObject['validationErrors'][0];
        $this->assertIsArray($validationErrorItem->systemErrors);
        $this->assertEquals('question', $validationErrorItem->entity);
    }

    /**
     * @testdox response contains exception errors
     */
    public function testExceptionErrors()
    {
        $responseObject = self::$responseObject;
        $this->assertIsArray($responseObject);
        $this->assertArrayHasKey('exceptionErrors', $responseObject);
        $this->assertNotEmpty($responseObject['exceptionErrors']);
        $exceptionErrorItem = $responseObject['exceptionErrors'][0];
        $this->assertEquals('Exception message', $exceptionErrorItem->error);
        $this->assertEquals('create', $exceptionErrorItem->op);
    }

    private static function getResponse()
    {
        $surveyResponse = DI::getContainer()->get(SurveyResponse::class);
        $surveyResponse->handleResponse(self::getTempIdMappingResponse());
        $surveyResponse->handleResponse(
            self::getValidationErrorResponse()
        );
        try {
            throw new PersistErrorException('Exception message');
        } catch (\Exception $e) {
            $surveyResponse->handleException($e, self::getOp());
        }

        return $surveyResponse->buildResponseObject();
    }

    private static function getOp()
    {
        return OpStandard::factory(
            'question',
            'create',
            12345,
            [
                'question' => [
                    'title' => 'G01Q01',
                    'type' => '1',
                    'question_theme_name' => 'arrays\/dualscale',
                    'gid' => '50',
                    'mandatory' => false
                ],
                'questionL10n' => [
                    'en' => [
                        'question' => 'foo',
                        'help'     => 'bar'
                    ]
                ]
            ],
            [
                'id' => 123456,
            ]
        );
    }

    private static function getValidationErrorResponse()
    {
        $op = self::getOp();
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        $opHandlerQuestionCreate = new OpHandlerQuestionCreate(
            $mockSet->modelQuestion,
            DI::getContainer()->get(
                TransformerInputQuestionAggregate::class
            )
        );
        return $opHandlerQuestionCreate->validateOperation($op);
    }

    private static function getTempIdMappingResponse()
    {
        $tempIdMapping = new TempIdMapping();
        $mapping['questionsMap'][] = new TempIdMapItem('tmp456', 1022, 'qid');
        $mapping['answersMap'][] = new TempIdMapItem('tmp567', 1023, 'aid');
        $mapping['answersMap'][] = new TempIdMapItem('tmp678', 1024, 'aid');
        $mapping['subquestionsMap'][] = new TempIdMapItem(
            'tmp789',
            1025,
            'qid'
        );
        $mapping['subquestionsMap'][] = new TempIdMapItem(
            'tmp890',
            1026,
            'qid'
        );
        foreach ($mapping as $groupName => $groupArray) {
            foreach ($groupArray as $tempIdMappingItem) {
                $tempIdMapping->addTempIdMapItem(
                    $tempIdMappingItem,
                    $groupName
                );
            }
        }
        return $tempIdMapping->getMappingResponseObject();
    }
}
