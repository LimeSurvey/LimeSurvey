<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputQuestionGroupReorder
};
use QuestionGroup;
use LimeSurvey\Models\Services\{
    QuestionGroupService,
    Exception\PermissionDeniedException,
    Exception\NotFoundException
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

/**
 * OpHandlerQuestionGroupReorder is responsible for reordering question groups
 * and also questions
 */
class OpHandlerQuestionGroupReorder implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerValidationTrait;

    protected string $entity;
    protected QuestionGroup $model;
    protected TransformerInputQuestionGroupReorder $transformer;

    public function __construct(
        QuestionGroup $model,
        TransformerInputQuestionGroupReorder $transformer
    ) {
        $this->entity = 'questionGroupReorder';
        $this->model = $model;
        $this->transformer = $transformer;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getEntityType() === $this->entity
            && $op->getType()->getId() === OpTypeUpdate::ID;
    }

    /**
     * Saves the changes to the database.
     * The patch should have the following structure:
     * "patch": [
     *      {
     *          "entity": "questionGroupReorder",
     *          "op": "update",
     *          "id": 123456,
     *          "props": {
     *              "50": { // question group id
     *                  "sortOrder": 2,
     *                  "questions": {
     *                      "723": { // question id
     *                          "sortOrder": 0
     *                      },
     *                      "722": { // question id
     *                          "sortOrder": 1
     *                      }
     *                  }
     *              },
     *              "59": {
     *                  "sortOrder": 1,
     *                  "questions": {
     *                      "726": {
     *                          "sortOrder": 3
     *                      },
     *                      "727": {
     *                          "sortOrder": 2
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * ]
     *
     * "questions" can also be left out or be empty.
     *  Also you don't have to pass all groups / all questions.
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionGroupService = $diContainer->get(
            QuestionGroupService::class
        );
        $surveyId = $this->getSurveyIdFromContext($op);
        $questionGroupService->checkUpdatePermission($surveyId);
        $transformedProps = $this->transformer->transformAll(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }
        $questionGroupService->reorderQuestionGroups(
            $surveyId,
            $transformedProps
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        $validationData = $this->validateCollectionIndex(
            $op,
            $validationData,
            false
        );
        if (empty($validationData)) {
            $validationData = $this->transformer->validateAll(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }
        return $this->getValidationReturn(
            gT('Could not reorder'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
