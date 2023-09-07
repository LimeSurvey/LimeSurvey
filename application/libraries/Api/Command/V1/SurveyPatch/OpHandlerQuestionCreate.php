<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpType\OpTypeCreate;

class OpHandlerQuestionCreate
{
    use OpHandlerSurveyTrait;

    protected string $entity;
    protected \Question $model;
    protected TransformerInputQuestion $transformer;
    protected TransformerInputQuestionL10ns $transformerL10n;
    protected TransformerInputQuestionAttribute $transformerAttribute;
    protected TransformerInputAnswer $transformerAnswer;

    public function __construct(
        \Question $model,
        TransformerInputQuestion $transformer,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputQuestionAttribute $transformerAttribute,
        TransformerInputAnswer $transformerAnswer
    ) {
        $this->entity = 'question';
        $this->model = $model;
        $this->transformer = $transformer;
        $this->transformerL10n = $transformerL10n;
        $this->transformerAttribute = $transformerAttribute;
        $this->transformerAnswer = $transformerAnswer;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isCreateOperation = $op->getType()->getId() === OpTypeCreate::ID;
        $isQuestionEntity = $op->getEntityType() === 'question';

        return $isCreateOperation && $isQuestionEntity;
    }

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
     * For proper creation all related entities must be contained in this props.
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
        $dataEntityTransformers = [
            'question'         => $this->transformer,
            'questionL10n'     => $this->transformerL10n,
            'advancedSettings' => $this->transformerAttribute,
            'answeroptions'    => $this->transformerAnswer,
            'subquestions'     => $this->transformer,
        ];

        foreach ($dataEntityTransformers as $name => $transformerClass) {
            $entityData = [];
            if (array_key_exists($name, $allData)) {
                $entityData = $transformerClass->transform($allData[$name]);
                // check transformed props
                $this->checkRequiredData($op, $entityData, $name);
            }
            $preparedData[$name] = $entityData;
        }

        return $preparedData;
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
            array_key_exists($name, $this->getRequiredEntitiesArray())
            && $data === null
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

    private function checkRawPropsForRequiredEntities(OpInterface $op, array $rawProps): void
    {
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
     * @return array
     */
    private function getRequiredEntitiesArray(): array
    {
        return [
            'question',
            'questionL10n',
            'advancedSettings',
        ];
    }
}
