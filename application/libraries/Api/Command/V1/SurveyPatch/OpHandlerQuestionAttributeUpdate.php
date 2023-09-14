<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use DI\DependencyException;
use DI\NotFoundException;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use SurveyLanguageSetting;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurveyLanguageSettings;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyTrait;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\SurveyUpdater\LanguageSettings;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;

class OpHandlerQuestionAttributeUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected string $entity;
    protected AttributesService $attributesService;
    protected QuestionService $questionService;
    protected TransformerInputQuestionAttribute $transformer;

    public function __construct(
        AttributesService $attributesService,
        QuestionService $questionService,
        TransformerInputQuestionAttribute $transformer
    ) {
        $this->entity = 'questionAttribute';
        $this->attributesService = $attributesService;
        $this->questionService = $questionService;
        $this->transformer = $transformer;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isAttributeEntity = $op->getEntityType() === $this->entity;

        return $isUpdateOperation && $isAttributeEntity;
    }

    /**
     * This handler accepts two different approaches to update the language settings:
     * Approach 1 (single language):
     * - in this case the language needs to be part of the id array
     * - patch structure:
     *      {
     *          "entity": "languageSetting",
     *          "op": "update",
     *          "id": {
     *              "sid": 123456,
     *              "language": "de"
     *          },
     *          "props": {
     *              "title": "Beispielfragebogen"
     *          }
     *      }
     *
     * Approach 2 (multiple languages):
     * - in this case the languages need to be indexes of the props array
     * - language must not be part of the id array
     * - patch structure:
     *      {
     *          "entity": "languageSetting",
     *          "op": "update",
     *          "id": {
     *              "sid": 123456
     *          },
     *          "props": {
     *              "de": {
     *                  "title": "Beispielfragebogen"
     *              },
     *              "en": {
     *                  "title": "Example Survey"
     *              }
     *          }
     *      }
     * @param OpInterface $op
     * @return void
     * @throws OpHandlerException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): void
    {

        $this->attributesService->saveAdvanced(
            $this->getQuestion($op),
            $this->getPreparedData($op)
        );
    }

    /**
     * Analyzes the patch, builds and returns the correct data structure
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     */
    public function getPreparedData(OpInterface $op)
    {
        $data = [];

        return $data;
    }

    /**
     * @param OpInterface $op
     * @param array|null $props
     * @return mixed
     * @throws OpHandlerException
     */
    private function getTransformedProps(OpInterface $op, ?array $props)
    {
        $transformedProps = $this->transformer->transform($props);
        if ($props === null || $transformedProps === null) {
            throw new OpHandlerException(sprintf(
                'No values to update for entity %s',
                $op->getEntityType()
            ));
        }
        return $transformedProps;
    }
}
