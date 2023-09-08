<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSubQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeCreate;

class OpHandlerQuestionCreate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected string $entity;
    protected \Question $model;
    protected TransformerInputQuestion $transformer;
    protected TransformerInputQuestionL10ns $transformerL10n;
    protected TransformerInputQuestionAttribute $transformerAttribute;
    protected TransformerInputAnswer $transformerAnswer;
    protected TransformerInputSubQuestion $transformerSubQuestion;
    protected TransformerInputQuestionAggregate $transformerInputQuestionAggregate;

    public function __construct(
        \Question $model,
        TransformerInputQuestion $transformer,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputQuestionAttribute $transformerAttribute,
        TransformerInputAnswer $transformerAnswer,
        TransformerInputSubQuestion $transformerSubQuestion,
        TransformerInputQuestionAggregate $transformerInputQuestionAggregate
    ) {
        $this->entity = 'question';
        $this->model = $model;
        $this->transformer = $transformer;
        $this->transformerL10n = $transformerL10n;
        $this->transformerAttribute = $transformerAttribute;
        $this->transformerAnswer = $transformerAnswer;
        $this->transformerSubQuestion = $transformerSubQuestion;
        $this->transformerInputQuestionAggregate = $transformerInputQuestionAggregate;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isCreateOperation = $op->getType()->getId() === OpTypeCreate::ID;
        $isQuestionEntity = $op->getEntityType() === 'question';

        return $isCreateOperation && $isQuestionEntity;
    }

    /**
     * For a valid creation of a question you need at least question and questionL10n entities within the patch.
     * An example patch with all possible entities:
     * {
     *     "entity": "question",
     *     "op": "create",
     *     "id": 0,
     *     "props": {
     *         "question": {
     *             "qid": "0",
     *             "title": "G01Q06",
     *             "type": "1",
     *             "question_theme_name": "arrays\/dualscale",
     *             "gid": "50",
     *             "mandatory": false,
     *             "relevance": "1",
     *             "encrypted": false,
     *             "save_as_default": false
     *         },
     *         "questionL10n": {
     *             "en": {
     *                 "question": "Array Question",
     *                 "help": "Help text"
     *             },
     *             "de": {
     *                 "question": "Array ger",
     *                 "help": "help ger"
     *             }
     *         },
     *         "advancedSettings": {
     *             "dualscale_headerA": {
     *                 "de": {
     *                     "value": "A ger"
     *                 },
     *                 "en": {
     *                     "value": "A"
     *                 }
     *             },
     *             "dualscale_headerB": {
     *                 "de": {
     *                     "value": "B ger"
     *                 },
     *                 "en": {
     *                     "value": "B"
     *                 }
     *             },
     *             "public_statistics": {
     *                 "": {
     *                     "value": "1"
     *                 }
     *             }
     *         },
     *         "answerOptions": {},
     *         "subQuestions": {}
     *     }
     * }
     * @param OpInterface $op
     * @return void
     * @throws OpHandlerException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionService = $diContainer->get(
            QuestionAggregateService::class
        );

        $questionService->save(
            $this->getSurveyIdFromContext($op),
            $this->prepareData($op)
        );
    }

    /**
     * For full creation all related entities must be contained in this props.
     *
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     */
    public function prepareData(OpInterface $op)
    {
        $allData = $op->getProps();
        $this->checkRawPropsForRequiredEntities($op, $allData);
        $preparedData = [];
        $dataEntityConfig = $this->getEntityConfig();

        foreach ($dataEntityConfig as $name => $config) {
            $entityData = [];
            if (array_key_exists($name, $allData)) {
                $transformerClass = $config['transformer'];
                if ($name == 'advancedSettings') {
                    $entityData = $this->prepareAdvancedSettings(
                        $op,
                        $allData['advancedSettings']
                    );
                } elseif ($config['isArray']) {
                    foreach ($allData[$name] as $index => $props) {
                        $entityData[$index] = $transformerClass->transform(
                            $props
                        );
                        $this->checkRequiredData(
                            $op,
                            $entityData[$index],
                            $name
                        );
                    }
                } else {
                    $entityData = $transformerClass->transform($allData[$name]);
                    $this->checkRequiredData($op, $entityData, $name);
                }
            }
            $preparedData[$name] = $entityData;
        }

        return $this->transformerInputQuestionAggregate->transform(
            $preparedData
        );
    }

    /**
     * @param OpInterface $op
     * @param array|null $data
     * @param string $name
     * @return void
     * @throws OpHandlerException
     */
    private function checkRequiredData(
        OpInterface $op,
        ?array $data,
        string $name
    ): void {
        if (
            in_array($name, $this->getRequiredEntitiesArray())
            && empty($data)
        ) {
            throw new OpHandlerException(
                sprintf(
                    'No values to update for %s in entity %s',
                    $name,
                    $op->getEntityType()
                )
            );
        }
    }

    private function checkRawPropsForRequiredEntities(
        OpInterface $op,
        array $rawProps
    ): void {
        foreach ($this->getRequiredEntitiesArray() as $requiredEntity) {
            if (!array_key_exists($requiredEntity, $rawProps)) {
                throw new OpHandlerException(
                    sprintf(
                        'Missing entity %s in props of %s',
                        $requiredEntity,
                        $op->getEntityType()
                    )
                );
            }
        }
    }

    /**
     * For creating a question without breaking the app, we need at least
     * "question"", "questionL10n" entities.
     * @return array
     */
    private function getRequiredEntitiesArray(): array
    {
        return [
            'question',
            'questionL10n'
        ];
    }

    private function getEntityConfig(): array
    {
        return [
            'question'         => [
                'transformer' => $this->transformer,
                'isArray'     => false
            ],
            'questionL10n'     => [
                'transformer' => $this->transformerL10n,
                'isArray'     => true
            ],
            'advancedSettings' => [
                'transformer' => $this->transformerAttribute,
                'isArray'     => false
            ],
            'answerOptions'    => [
                'transformer' => $this->transformerAnswer,
                'isArray'     => true
            ],
            'subQuestions'     => [
                'transformer' => $this->transformerSubQuestion,
                'isArray'     => true
            ],
        ];
    }

    /**
     * Converts the advanced settings from the raw data to the expected format.
     * @param OpInterface $op
     * @param $data
     * @return array
     */
    private function prepareAdvancedSettings(OpInterface $op, ?array $data)
    {
        $preparedSettings = [];
        foreach ($data as $attrName => $languages) {
            foreach ($languages as $lang => $advancedSetting) {
                $transformedSetting = $this->transformerAttribute->transform(
                    $advancedSetting
                );
                if (array_key_exists('value', $transformedSetting)) {
                    $value = $transformedSetting['value'];
                    if ($lang !== '') {
                        $preparedSettings[0][$attrName][$lang] = $value;
                    } else {
                        $preparedSettings[0][$attrName] = $value;
                    }
                }
            }
        }
        return $preparedSettings;
    }
}
