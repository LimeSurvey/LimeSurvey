<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\{
    Input\TransformerInputQuestionAggregate
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerQuestionTrait,
    OpHandlerExceptionTrait
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
    use OpHandlerQuestionTrait;
    use OpHandlerExceptionTrait;

    protected string $entity;
    protected Question $model;
    protected TransformerInputQuestionAggregate $transformerInputQuestionAggregate;

    public function __construct(
        Question $model,
        TransformerInputQuestionAggregate $transformerInputQuestionAggregate
    ) {
        $this->entity = 'question';
        $this->model = $model;
        $this->transformerInputQuestionAggregate = $transformerInputQuestionAggregate;
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
     *                             "de": {
     *                                 "value": "A ger"
     *                             },
     *                             "en": {
     *                                 "value": "A"
     *                             }
     *                     },
     *                     "dualscale_headerB": {
     *                             "de": {
     *                                 "value": "B ger"
     *                             },
     *                             "en": {
     *                                 "value": "B"
     *                             }
     *                     },
     *                     "public_statistics": {
     *                             "": {
     *                                 "value": "1"
     *                             }
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
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     */
    public function handle(OpInterface $op): array
    {
        $this->throwTransformerValidationErrors(
            $this->transformerInputQuestionAggregate->validate(
            $op->getProps(),
            ),
            $op
        );
        $transformedProps = $this->transformerInputQuestionAggregate->transform(
            $op->getProps()
        );
        $tempId = $this->extractTempId($transformedProps['question']);
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionService = $diContainer->get(
            QuestionAggregateService::class
        );

        $question = $questionService->save(
            $this->getSurveyIdFromContext($op),
            $transformedProps
        );

        return array_merge(
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
                $transformedProps['subquestions']
            ),
            $this->getSubQuestionNewIdMapping(
                $question,
                $transformedProps['answeroptions'],
                true
            )
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // check for existing tempId props in question,
        // subquestion and/or answer when operation validation 2.0 is developed

        return true;
    }
}
