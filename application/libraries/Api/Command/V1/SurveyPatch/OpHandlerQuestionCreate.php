<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\{
    Input\TransformerInputAnswer,
    Input\TransformerInputAnswerL10ns,
    Input\TransformerInputQuestion,
    Input\TransformerInputQuestionAggregate,
    Input\TransformerInputQuestionAttribute,
    Input\TransformerInputQuestionL10ns,
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapItem;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerQuestionTrait
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

    protected string $entity;
    protected Question $model;
    protected TransformerInputQuestion $transformer;
    protected TransformerInputQuestionL10ns $transformerL10n;
    protected TransformerInputQuestionAttribute $transformerAttribute;
    protected TransformerInputAnswer $transformerAnswer;
    protected TransformerInputAnswerL10ns $transformerAnswerL10n;
    protected TransformerInputQuestionAggregate $transformerInputQuestionAggregate;

    public function __construct(
        Question $model,
        TransformerInputQuestion $transformer,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputQuestionAttribute $transformerAttribute,
        TransformerInputAnswer $transformerAnswer,
        TransformerInputAnswerL10ns $transformerAnswerL10n,
        TransformerInputQuestionAggregate $transformerInputQuestionAggregate
    ) {
        $this->entity = 'question';
        $this->model = $model;
        $this->transformer = $transformer;
        $this->transformerL10n = $transformerL10n;
        $this->transformerAttribute = $transformerAttribute;
        $this->transformerAnswer = $transformerAnswer;
        $this->transformerAnswerL10n = $transformerAnswerL10n;
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
        $transformedProps = $this->prepareData($op);
        if (
            !is_array($transformedProps) ||
            !array_key_exists(
                'question',
                $transformedProps
            )
        ) {
            throw new OpHandlerException(
                sprintf(
                    'no question entity provided within props for %s with id "%s"',
                    $this->entity,
                    print_r($op->getEntityId(), true)
                )
            );
        }
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
     * Aggregates the transformed data of all the different entities into
     * a single array as the service expects it.
     * @param OpInterface $op
     * @return ?mixed
     * @throws OpHandlerException
     */
    public function prepareData(OpInterface $op)
    {
        $allData = $op->getProps();
        $this->checkRawPropsForRequiredEntities($op, $allData);
        $preparedData = [];
        $entities = [
            'question',
            'questionL10n',
            'attributes',
            'answers',
            'subquestions'
        ];

        foreach ($entities as $name) {
            $entityData = [];
            if (array_key_exists($name, $allData)) {
                $entityData = $this->prepare($op, $name, $allData[$name]);
            }
            $preparedData[$name] = $entityData;
        }

        return $this->transformerInputQuestionAggregate->transform(
            $preparedData
        );
    }

    /**
     * Prepares the data structure for the different entities by calling
     * the different prepare functions.
     * @param OpInterface $op
     * @param string $name
     * @param array $data
     * @return array|mixed|null
     * @throws OpHandlerException
     */
    public function prepare(OpInterface $op, string $name, array $data)
    {
        switch ($name) {
            case 'question':
                $entityData = $this->transformer->transform($data);
                $this->checkRequiredData($op, $entityData, 'question');
                return $entityData;
            case 'questionL10n':
                return $this->prepareQuestionL10n($op, $data);
            case 'attributes':
                return $this->prepareAdvancedSettings(
                    $op,
                    $data
                );
            case 'answers':
                return $this->prepareAnswers(
                    $op,
                    $data,
                    $this->transformerAnswer,
                    $this->transformerAnswerL10n
                );
            case 'subquestions':
                return $this->prepareSubQuestions(
                    $op,
                    $this->transformer,
                    $this->transformerL10n,
                    $data
                );
        }
        return $data;
    }

    /**
     * Checks the raw props for all required entities.
     * @param OpInterface $op
     * @param array $rawProps
     * @return void
     * @throws OpHandlerException
     */
    private function checkRawPropsForRequiredEntities(
        OpInterface $op,
        array $rawProps
    ): void {
        foreach ($this->getRequiredEntitiesArray() as $requiredEntity) {
            if (!array_key_exists($requiredEntity, $rawProps)) {
                $this->throwRequiredParamException($op, $requiredEntity);
            }
        }
    }

    /**
     * @param OpInterface $op
     * @param array|null $data
     * @return array
     * @throws OpHandlerException
     */
    private function prepareQuestionL10n(OpInterface $op, ?array $data): array
    {
        $preparedL10n = [];
        if (is_array($data)) {
            foreach ($data as $index => $props) {
                $preparedL10n[$index] = $this->transformerL10n->transform(
                    $props
                );
                $this->checkRequiredData(
                    $op,
                    $preparedL10n[$index],
                    'questionL10n'
                );
            }
        }

        return $preparedL10n;
    }

    /**
     * Converts the advanced settings from the raw data to the expected format.
     * @param OpInterface $op
     * @param array|null $data
     * @return array
     * @throws OpHandlerException
     */
    private function prepareAdvancedSettings(
        OpInterface $op,
        ?array $data
    ): array {
        $preparedSettings = [];
        if (is_array($data)) {
            foreach ($data as $attrName => $languages) {
                foreach ($languages as $lang => $advancedSetting) {
                    $transformedSetting = $this->transformerAttribute->transform(
                        $advancedSetting
                    );
                    $this->checkRequiredData(
                        $op,
                        $transformedSetting,
                        'attributes'
                    );
                    if (
                        is_array($transformedSetting) && array_key_exists(
                            'value',
                            $transformedSetting
                        )
                    ) {
                        $value = $transformedSetting['value'];
                        if ($lang !== '') {
                            $preparedSettings[0][$attrName][$lang] = $value;
                        } else {
                            $preparedSettings[0][$attrName] = $value;
                        }
                    }
                }
            }
        }
        return $preparedSettings;
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
