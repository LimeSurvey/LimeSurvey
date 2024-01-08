<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerSurveyTrait;
use QuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroup;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\QuestionGroupService;
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

    protected string $entity;
    protected QuestionGroup $model;
    protected TransformerInterface $transformerGroup;
    protected TransformerInterface $transformerQuestion;

    public function __construct(
        QuestionGroup $model,
        TransformerInputQuestionGroup $transformerGroup,
        TransformerInputQuestion $transformerQuestion
    ) {
        $this->entity = 'questionGroupReorder';
        $this->model = $model;
        $this->transformerGroup = $transformerGroup;
        $this->transformerQuestion = $transformerQuestion;
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
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionGroupService = $diContainer->get(
            QuestionGroupService::class
        );
        $questionGroupService->reorderQuestionGroups(
            $this->getSurveyIdFromContext($op),
            $this->getGroupReorderData($op)
        );
    }

    /**
     * Gets the props from the request and restructures the data to be suitable
     * for the reordering function.
     * @param OpInterface $op
     * @return array
     */
    public function getGroupReorderData(OpInterface $op)
    {
        $groupReorderData = [];
        $i = 0;
        foreach ($op->getProps() as $gid => $groupData) {
            $k = 0;
            if (is_numeric($gid) && $gid > 0) {
                $groupData['gid'] = $gid;
            }
            $tfGroupData = $this->transformerGroup->transform($groupData);
            $this->checkGroupReorderData($op, $tfGroupData, 'group');
            $groupReorderData[$i] = $tfGroupData;
            if (array_key_exists('questions', $groupData)) {
                foreach ($groupData['questions'] as $qid => $questionData) {
                    $questionData['gid'] = $gid;
                    $questionData['qid'] = $qid;
                    $tfQuestionData = $this->transformerQuestion->transform(
                        $questionData
                    );
                    $this->checkGroupReorderData(
                        $op,
                        $tfQuestionData,
                        'question'
                    );
                    $groupReorderData[$i]['questions'][$k] = $tfQuestionData;
                    $k++;
                }
            }
            $i++;
        }
        return $groupReorderData;
    }

    /**
     * @param OpInterface $op
     * @param array|null $data
     * @param string $type
     * @return void
     * @throws OpHandlerException
     */
    private function checkGroupReorderData(
        OpInterface $op,
        ?array $data,
        string $type
    ) {
        $requiredForGroup = ['gid', 'group_order'];
        $requiredForQuestion = ['qid', 'gid', 'question_order'];
        $required = $type === 'group' ? $requiredForGroup : $requiredForQuestion;
        if (!is_array($data)) {
            $this->throwNoValuesException($op);
        }
        foreach ($required as $param) {
            /** @var array $data */
            if (!array_key_exists($param, $data)) {
                $this->throwRequiredParamException($op, $param);
            }
        }
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // checkGroupReorderData is doing validation at a later stage
        return true;
    }
}
