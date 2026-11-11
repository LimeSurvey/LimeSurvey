<?php

namespace ls\tests\unit\objectpatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionCreate;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\ExceptionErrors;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\SurveyResponse;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapItem;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapping;
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\ValidationErrors;
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

    /**
     * @testdox exception error contains validation errors of the error model
     */
    public function testExceptionErrorContainsErrorModelErrors()
    {
        $errorModel = new \SurveyLanguageSetting();
        $errorModel->addError('surveyls_description', 'Links are not allowed.');
        $errorModel->addError('surveyls_welcometext', 'Links are not allowed.');
        $exception = new PersistErrorException('Failed saving language settings');
        $exception->setErrorModel($errorModel);

        $surveyResponse = new SurveyResponse(
            new TempIdMapping(),
            new ValidationErrors(),
            new ExceptionErrors()
        );
        $surveyResponse->handleException($exception, self::getOp());
        $responseObject = $surveyResponse->buildResponseObject();

        $this->assertEquals(
            'Failed saving language settings: Links are not allowed.',
            $responseObject['exceptionErrors'][0]->error
        );
    }

    /**
     * Checks that the additional data of every operation of a patch is merged
     * into 'extras': Keyed data of earlier operations is kept, a later value
     * of the same key wins, and the result stays a JSON object keyed by qid.
     * @testdox response merges the additional data of every operation
     * @return void nothing: The checks are PHPUnit assertions
     */
    public function testAdditionalOfEveryOperationIsMerged(): void
    {
        $surveyResponse = new SurveyResponse(
            new TempIdMapping(),
            new ValidationErrors(),
            new ExceptionErrors()
        );
        $surveyResponse->handleResponse(
            ['additional' => ['questionRelevance' => [12 => 'Q1 == "A1"']]]
        );
        $surveyResponse->handleResponse([]);
        $surveyResponse->handleResponse(
            ['additional' => ['questionRelevance' => [3 => '1']]]
        );
        $surveyResponse->handleResponse(
            ['additional' => ['questionRelevance' => [12 => 'Q2 == "A2"']]]
        );

        $responseObject = $surveyResponse->buildResponseObject();

        $this->assertSame(
            ['questionRelevance' => [12 => 'Q2 == "A2"', 3 => '1']],
            $responseObject['extras']
        );
        $this->assertArrayNotHasKey('extrasList', $responseObject);
        $this->assertSame(
            '{"questionRelevance":{"12":"Q2 == \\"A2\\"","3":"1"}}',
            json_encode($responseObject['extras'])
        );
    }

    /**
     * Checks when the patch reports new response fields: Created questions or
     * subquestions do, other created entities don't.
     * @testdox response knows when questions or subquestions were created
     * @return void nothing: The checks are PHPUnit assertions
     */
    public function testHasCreatedQuestionFields(): void
    {
        $responseFor = function (array $mapping): SurveyResponse {
            $surveyResponse = new SurveyResponse(
                new TempIdMapping(),
                new ValidationErrors(),
                new ExceptionErrors()
            );
            $surveyResponse->handleResponse(['tempIdMapping' => $mapping]);
            return $surveyResponse;
        };

        $this->assertFalse($responseFor([])->hasCreatedQuestionFields());
        $this->assertFalse(
            $responseFor(['answersMap' => [new TempIdMapItem('temp__1', 5, 'aid')]])
                ->hasCreatedQuestionFields()
        );
        $this->assertTrue(
            $responseFor(['questionsMap' => [new TempIdMapItem('temp__2', 6, 'qid')]])
                ->hasCreatedQuestionFields()
        );
        $this->assertTrue(
            $responseFor(['subquestionsMap' => [new TempIdMapItem('temp__3', 7, 'qid')]])
                ->hasCreatedQuestionFields()
        );
    }

    /**
     * Checks that the field names are returned in 'extras', keyed by qid, next
     * to the other extras, and that an empty list adds nothing.
     * @testdox response returns the question field names in extras
     * @return void nothing: The checks are PHPUnit assertions
     */
    public function testSetQuestionFieldnames(): void
    {
        $surveyResponse = new SurveyResponse(
            new TempIdMapping(),
            new ValidationErrors(),
            new ExceptionErrors()
        );
        $surveyResponse->setQuestionFieldnames([]);
        $this->assertArrayNotHasKey('extras', $surveyResponse->buildResponseObject());

        $surveyResponse->handleResponse(
            ['additional' => ['questionRelevance' => [12 => '1']]]
        );
        $fieldnames = [6 => [['fieldname' => 'Q6', 'qid' => 6]]];
        $surveyResponse->setQuestionFieldnames($fieldnames);

        $extras = $surveyResponse->buildResponseObject()['extras'];
        $this->assertSame($fieldnames, $extras['questionFieldnames']);
        $this->assertSame([12 => '1'], $extras['questionRelevance']);
        $this->assertSame(
            '{"6":[{"fieldname":"Q6","qid":6}]}',
            json_encode($extras['questionFieldnames'])
        );
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
