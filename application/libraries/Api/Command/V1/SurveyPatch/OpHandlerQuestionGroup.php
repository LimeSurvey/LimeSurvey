<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Api\Command\V1\SurveyPatch\{
    Traits\OpHandlerExceptionTrait,
    Traits\OpHandlerSurveyTrait,
    Traits\OpHandlerValidationTrait,
    Response\TempIdMapItem
};
use QuestionGroup;
use LimeSurvey\Models\Services\{
    QuestionGroupService,
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException
};
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputQuestionGroupAggregate
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeCreate,
    OpType\OpTypeDelete,
    OpType\OpTypeUpdate
};

class OpHandlerQuestionGroup implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerValidationTrait;

    protected string $entity;
    protected QuestionGroup $model;
    protected QuestionGroupService $questionGroupService;
    protected TransformerInputQuestionGroupAggregate $transformer;

    private bool $isUpdateOperation = false;
    private bool $isCreateOperation = false;
    private bool $isDeleteOperation = false;

    public function __construct(
        QuestionGroup $model,
        QuestionGroupService $questionGroupService,
        TransformerInputQuestionGroupAggregate $transformer
    ) {
        $this->entity = 'questionGroup';
        $this->model = $model;
        $this->questionGroupService = $questionGroupService;
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
        $this->setOperationTypes($op);
        $isQuestionGroupEntity = $op->getEntityType() === $this->entity;

        return (
            $this->isUpdateOperation
            || $this->isCreateOperation
            || $this->isDeleteOperation
        )
            && $isQuestionGroupEntity;
    }

    /**
     * Saves the changes to the database.
     *
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     * @throws PermissionDeniedException
     * @throws NotFoundException
     * @throws PersistErrorException
     * @throws TransformerException
     */
    public function handle(OpInterface $op): array
    {
        switch (true) {
            case $this->isUpdateOperation:
                $this->update($op);
                break;
            case $this->isCreateOperation:
                return $this->create($op);
            case $this->isDeleteOperation:
                $this->delete($op);
                break;
        }
        return [];
    }

    /**
     * Reads the operation type from the given operation,
     * and sets the corresponding flags.
     *
     * @param OpInterface $op
     * @return void
     */
    public function setOperationTypes(OpInterface $op)
    {
        $this->isUpdateOperation
            = $op->getType()->getId() === OpTypeUpdate::ID;
        $this->isCreateOperation
            = $op->getType()->getId() === OpTypeCreate::ID;
        $this->isDeleteOperation
            = $op->getType()->getId() === OpTypeDelete::ID;
    }

    /**
     * Update question group
     *
     * For update of a question group the patch should look like this:
     * {
     *    "patch": [
     *         {
     *             "entity": "questionGroup",
     *             "op": "update",
     *             "id": 7,
     *             "props": {
     *                 "questionGroup": {
     *                     "randomizationGroup": "",
     *                     "gRelevance": ""
     *                 },
     *                 "questionGroupL10n": {
     *                     "en": {
     *                         "groupName": "3rd Group - updated",
     *                         "description": "English"
     *                     },
     *                     "fr": {
     *                         "groupName": "Troisième Groupe - updated",
     *                         "description": "French"
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * @param OpInterface $op
     * @param QuestionGroupService $groupService
     * @return void
     * @throws OpHandlerException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     * @throws TransformerException
     */
    private function update(OpInterface $op)
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionGroupService->checkUpdatePermission($surveyId);
        $transformedProps = $this->transformer->transform(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }
        $questionGroup = $this->questionGroupService->getQuestionGroupForUpdate(
            $surveyId,
            $op->getEntityId()
        );
        if (!empty($transformedProps['questionGroup'])) {
            $this->questionGroupService->updateQuestionGroup(
                $questionGroup,
                $transformedProps['questionGroup']
            );
        }
        if (!empty($transformedProps['questionGroupI10N'])) {
            $this->questionGroupService->updateQuestionGroupLanguages(
                $questionGroup,
                $transformedProps['questionGroupI10N']
            );
        }
    }

    /**
     * To fully create a new question group, the dataset should have
     * this structure for props:
     *
     * {
     *     "patch": [
     *         {
     *             "entity": "questionGroup",
     *             "op": "create",
     *             "props":{
     *                 "questionGroup": {
     *                     "tempId": 777,
     *                     "randomizationGroup": "",
     *                     "gRelevance": ""
     *                 },
     *                 "questionGroupL10n": {
     *                     "en": {
     *                         "groupName": "3rd Group",
     *                         "description": "English"
     *                     },
     *                     "fr": {
     *                         "groupName": "Troisième Groupe",
     *                         "description": "French"
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * If those questionGroup and questionGroupL10n properties are missing,
     * and the structure resembles the usual update structure,
     * only a basic question group will be created. Language specific data must
     * then be passed in a different patch operation.
     *
     * @param OpInterface $op
     * @return array
     * @throws NotFoundException
     * @throws OpHandlerException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     * @throws TransformerException
     */
    private function create(OpInterface $op): array
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionGroupService->checkCreatePermission($surveyId);
        $transformedProps = $this->transformer->transform(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        ) ?? [];
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }
        $questionGroupData = $transformedProps['questionGroup'] ?? [];
        $tempId = $this->extractTempId($questionGroupData);
        $questionGroup = $this->questionGroupService->createGroup(
            $surveyId,
            $transformedProps
        );
        $questionGroup->refresh();
        return [
            'tempIdMapping' => [
                'questionGroupsMap' => [
                    new TempIdMapItem(
                        $tempId,
                        $questionGroup->gid,
                        'gid'
                    )
                ]
            ]
        ];
    }

    /**
     * To delete a question group, the dataset should look like this
     * {
     *    "patch": [
     *        {
     *            "entity": "questionGroup",
     *            "op": "delete",
     *            "id": 7
     *        }
     *    ]
     * }
     *
     * @param OpInterface $op
     * @return void
     * @throws PermissionDeniedException
     */
    private function delete(OpInterface $op)
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionGroupService->checkDeletePermission($surveyId);
        $this->questionGroupService->deleteGroup(
            $op->getEntityId(),
            $surveyId
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $this->setOperationTypes($op);
        $validationData = [];
        $validationData = $this->validateSurveyIdFromContext(
            $op,
            $validationData
        );
        if ($this->isUpdateOperation || $this->isCreateOperation) {
            $validationData = $this->transformer->validate(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }
        if ($this->isUpdateOperation || $this->isDeleteOperation) {
            $validationData = $this->validateEntityId(
                $op,
                !is_array($validationData) ? [] : $validationData
            );
        }
        $error = gT('Could not save question group');
        if ($this->isDeleteOperation) {
            $error = gT('Could not delete question group');
        }

        return $this->getValidationReturn(
            $error,
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
