<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeCreate;
use LimeSurvey\ObjectPatch\OpType\OpTypeDelete;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;

class OpHandlerQuestionGroup implements OpHandlerInterface
{
    protected TransformerInterface $transformer;
    protected TransformerInterface $transformerI10N;
    protected string $entity;
    protected CModel $model;

    private bool $isUpdateOperation = false;
    private bool $isCreateOperation = false;
    private bool $isDeleteOperation = false;

    public function __construct(
        string $entity,
        CModel $model,
        TransformerInterface $transformer,
        TransformerInterface $transformerI10N
    ) {
        $this->entity = $entity;
        $this->model = $model;
        $this->transformer = $transformer;
        $this->transformerI10N = $transformerI10N;
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
        $isQuestionGroupEntity = $op->getEntityType() === 'questionGroup';

        return
            (
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
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionGroupService = $diContainer->get(
            QuestionGroupService::class
        );

        switch (true) {
            case $this->isUpdateOperation:
                $this->update($op, $questionGroupService);
                break;
            case $this->isCreateOperation:
                $this->create($op, $questionGroupService);
                break;
            case $this->isDeleteOperation:
                $this->delete($op, $questionGroupService);
                break;
        }
    }

    /**
     * Reads the operation type from the given operation,
     * and sets the corresponding flags.
     * @param OpInterface $op
     * @return void
     */
    public function setOperationTypes(OpInterface $op)
    {
        $this->isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $this->isCreateOperation = $op->getType()->getId() === OpTypeCreate::ID;
        $this->isDeleteOperation = $op->getType()->getId() === OpTypeDelete::ID;
    }

    /**
     * Makes use of the transformers dependent on the passed structure of props
     * @param OpInterface $op
     * @return array|mixed
     * @throws OpHandlerException
     */
    public function getTransformedProps(OpInterface $op)
    {
        $transformedProps = [];
        $props = $op->getProps();
        if (
            array_key_exists('questionGroupI10N', $props)
            &&
            array_key_exists('questionGroup', $props)
        ) {
            $transformedProps['questionGroup'] = $this->transformer->transform(
                $props['questionGroup']
            );
            foreach ($props['questionGroupI10N'] as $lang => $questionGroupI10N) {
                $transformedProps['questionGroupI10N'][$lang] = $this->transformerI10N->transform(
                    $questionGroupI10N
                );
            }
        } else {
            $transformedProps = $this->transformer->transform($props);
        }
        if ($props === null || $transformedProps === null) {
            throw new OpHandlerException(
                printf(
                    'No values to update for entity %s',
                    $op->getEntityType()
                )
            );
        }
        return $transformedProps;
    }

    /**
     * Extracts and returns surveyId from context
     * @param OpInterface $op
     * @return int
     * @throws OpHandlerException
     */
    private function getSurveyIdFromContext(OpInterface $op)
    {
        $context = $op->getContext();
        $surveyId = $context['id'] ? (int)$context['id'] : null;
        if ($surveyId === null) {
            throw new OpHandlerException(
                printf(
                    'Missing survey id in context for entity %s',
                    $op->getEntityType()
                )
            );
        }
        return $surveyId;
    }

    /**
     * For update of a question group the patch should look like this:
     *  {
     *      "entity": "questionGroup",
     *      "op": "update",
     *      "id": {
     *              "gid": 43
     *      },
     *      "props": {
     *          "groupOrder": 1000
     *      }
     *  }
     * @param OpInterface $op
     * @param QuestionGroupService $groupService
     * @return void
     * @throws OpHandlerException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     */
    private function update(OpInterface $op, QuestionGroupService $groupService)
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $transformedProps = $this->getTransformedProps($op);
        $questionGroup = $groupService->getQuestionGroupForUpdate(
            $surveyId,
            $this->getQuestionGroupId($op)
        );
        $groupService->updateQuestionGroup(
            $questionGroup,
            $transformedProps
        );
    }

    /**
     * To fully create a new question group, the dataset should have
     * this structure for props:
     * {
     *      "questionGroup": {
     *          "sid": "113258",
     *          "randomizationGroup": "",
     *          "gRelevance": ""
     *      },
     *      "questionGroupI10N": {
     *          "en": {
     *              "groupName": "3rd Group",
     *              "description": "English"
     *          },
     *          "de": {
     *              "groupName": "Dritte Gruppe",
     *              "description": "Deutsch"
     *          }
     *      }
     * }
     * If those questionGroup and questionGroupI10N properties are missing, and the structure resembles
     * the usual update structure,
     * only a basic question group will be created.
     * language specific data must then be passed in a different patch operation.
     * @param OpInterface $op
     * @param QuestionGroupService $groupService
     * @return void
     * @throws OpHandlerException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     */
    private function create(OpInterface $op, QuestionGroupService $groupService)
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $transformedProps = $this->getTransformedProps($op);
        $groupService->createGroup($surveyId, $transformedProps);
    }

    /**
     * To delete a question group, the dataset should look like this
     *  {
     *      "entity": "questionGroup",
     *      "op": "delete",
     *      "id": {
     *          "gid": 43
     *      }
     *  }
     * @param OpInterface $op
     * @param QuestionGroupService $groupService
     * @return void
     */
    private function delete(OpInterface $op, QuestionGroupService $groupService)
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $groupService->deleteGroup(
            $this->getQuestionGroupId($op),
            $surveyId
        );
    }

    /**
     * Extracts and returns gid (question group id) from passed id parameter
     * @param OpInterface $op
     * @return int
     * @throws OpHandlerException
     **/
    private function getQuestionGroupId(OpInterface $op)
    {
        $id = $op->getEntityId();
        if (is_array($id)) {
            if (array_key_exists('gid', $id)) {
                $id = $id['gid'];
            } else {
                throw new OpHandlerException('no gid provided');
            }
        }
        return $id;
    }
}
