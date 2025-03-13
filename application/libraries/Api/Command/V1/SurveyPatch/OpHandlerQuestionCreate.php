<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use DI\DependencyException;
use DI\NotFoundException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\Transformer\{
    Input\TransformerInputQuestionAggregate
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapItem;
use LimeSurvey\Models\Services\Exception\{
    PermissionDeniedException,
    PersistErrorException
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{OpHandlerSurveyTrait,
    OpHandlerExceptionTrait,
    OpHandlerQuestionTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpType\OpTypeCreate,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface
};
use LimeSurvey\Models\Services\QuestionAggregateService;
use Question;

class OpHandlerQuestionCreate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerQuestionTrait;
    use OpHandlerValidationTrait;

    protected string $entity;
    protected Question $model;
    protected TransformerInputQuestionAggregate $transformer;

    public function __construct(
        Question $model,
        TransformerInputQuestionAggregate $transformer
    ) {
        $this->entity = 'question';
        $this->model = $model;
        $this->transformer = $transformer;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isCreateOperation = $op->getType()->getId() === OpTypeCreate::ID;
        $isQuestionEntity = $op->getEntityType() === 'question';

        return $isCreateOperation && $isQuestionEntity;
    }

    /**
     * For a valid creation of a question you need at least question and
     * questionL10n entities within the patch.
     * An example patch with all(!) possible entities:
     *
     * {
     *     "patch": [
     *         {
     *             "entity": "question",
     *             "op": "create",
     *             "id": "0",
     *             "props": {
     *                 "question": {
     *                     "qid": "0",
     *                     "title": "G01Q06",
     *                     "type": "1",
     *                     "questionThemeName": "arrays\/dualscale",
     *                     "gid": "1",
     *                     "mandatory": false,
     *                     "relevance": "1",
     *                     "encrypted": false,
     *                     "saveAsDefault": false,
     *                     "tempId": "XXX321"
     *                 },
     *                 "questionL10n": {
     *                     "en": {
     *                             "question": "Array Question",
     *                             "help": "Help text"
     *                     },
     *                     "de": {
     *                             "question": "Array ger",
     *                             "help": "help ger"
     *                     }
     *                 },
     *                 "attributes": {
     *                     "dualscale_headerA": {
     *                             "de": "A ger",
     *                             "en": "A"
     *                     },
     *                     "dualscale_headerB": {
     *                             "de": "B ger",
     *                             "en": "B"
     *                     },
     *                     "public_statistics": {
     *                             "": "1"
     *                     }
     *                 },
     *                 "answers": {
     *                     "0": {
     *                         "code": "AO01",
     *                         "sortOrder": 0,
     *                         "assessmentValue": 0,
     *                         "scaleId": 0,
     *                         "tempId": "111",
     *                         "l10ns": {
     *                             "de": {
     *                                 "answer": "antwort1",
     *                                 "language": "de"
     *                             },
     *                             "en": {
     *                                 "answer": "answer1",
     *                                 "language": "en"
     *                             }
     *                         }
     *                     },
     *                     "1": {
     *                         "code": "AO02",
     *                         "sortOrder": 1,
     *                         "assessmentValue": 0,
     *                         "scaleId": 0,
     *                         "tempId": "112",
     *                         "l10ns": {
     *                             "de": {
     *                                 "answer": "antwort1.2",
     *                                 "language": "de"
     *                             },
     *                             "en": {
     *                                 "answer": "answer1.2",
     *                                 "language": "en"
     *                             }
     *                         }
     *                     }
     *                 },
     *                 "subquestions": {
     *                     "0": {
     *                         "title": "SQ001",
     *                         "sortOrder": 0,
     *                         "relevance": "1",
     *                         "tempId": "113",
     *                         "l10ns": {
     *                             "de": {
     *                                 "question": "subger1",
     *                                 "language": "de"
     *                             },
     *                             "en": {
     *                                 "question": "sub1",
     *                                 "language": "en"
     *                             }
     *                         }
     *                     },
     *                     "1": {
     *                         "title": "SQ002",
     *                         "sortOrder": 1,
     *                         "relevance": "1",
     *                         "tempId": "114",
     *                         "l10ns": {
     *                             "de": {
     *                                 "question": "subger2",
     *                                 "language": "de"
     *                             },
     *                             "en": {
     *                                 "question": "sub2",
     *                                 "language": "en"
     *                             }
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws TransformerException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     * @throws \CException
     */
    public function handle(OpInterface $op): array
    {
        $questionService = DI::getContainer()->get(
            QuestionAggregateService::class
        );
        $surveyId = $this->getSurveyIdFromContext($op);
        $questionService->checkUpdatePermission($surveyId);
        $transformOptions = ['operation' => $op->getType()->getId()];
        $data = $this->transformer->transform(
            $op->getProps(),
            $transformOptions
        ) ?? [];
        if (empty($data)) {
            $this->throwNoValuesException($op);
        }
        $questionData = $data['question'] ?? [];
        $subQuestionsData = $data['subquestions'] ?? [];
        $answerOptionsData = $data['answeroptions'] ?? [];

        $tempId = $this->extractTempId($questionData);

        $question = $questionService->save(
            $surveyId,
            $data,
            true
        );

        $mapping = array_merge(
            [
                'questionsMap' => [
                    new TempIdMapItem(
                        $tempId,
                        $question->qid,
                        'qid'
                    )
                ]
            ],
            $this->getSubQuestionNewIdMapping(
                $question,
                $subQuestionsData
            ),
            $this->getSubQuestionNewIdMapping(
                $question,
                $answerOptionsData,
                true
            )
        );
        return ['tempIdMapping' => $mapping];
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        $validationData = $this->validateCollectionIndex($op, $validationData);
        if (empty($validationData)) {
            $validationData = $this->transformer->validate(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }
        return $this->getValidationReturn(
            gT('Could not create question'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
