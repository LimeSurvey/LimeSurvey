<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use CModel;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroup;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;

/**
 * OpHandlerQuestionGroupReorder is responsible for reordering question groups
 * and also questions
 */
class OpHandlerQuestionGroupReorder implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected TransformerInterface $transformerGroup;
    protected TransformerInterface $transformerQuestion;
    protected string $entity;
    protected CModel $model;

    public function __construct(
        string $entity,
        CModel $model,
        TransformerInputQuestionGroup $transformerGroup,
        TransformerInputQuestion $transformerQuestion
    ) {
        $this->entity = $entity;
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
        return $op->getEntityType() === 'questionGroupReorder'
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
            throw new OpHandlerException(
                sprintf(
                    'No values to update for entity "%s"',
                    $op->getEntityType()
                )
            );
        }
        foreach ($required as $param) {
            if (!array_key_exists($param, $data)) {
                throw new OpHandlerException(
                    sprintf(
                        'Required parameter "%s" is missing. Entity "%s"',
                        $param,
                        $op->getEntityType()
                    )
                );
            }
        }
    }
}
