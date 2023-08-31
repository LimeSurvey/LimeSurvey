<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use CModel;
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
        TransformerInterface $transformerGroup,
        TransformerInterface $transformerQuestion
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
     *          "id": 981949, // not relevant at all
     *          "props": {
     *              "50" : {
     *                  "gid": "50",
     *                  "groupOrder": "0",
     *                  "questions": [
     *                      {
     *                      "qid": "722",
     *                      "gid": "50",
     *                      "questionOrder": "2"
     *                      },
     *                      {
     *                      "qid": "723",
     *                      "gid": "50",
     *                      "questionOrder": "1"
     *                      }
     *                  ]
     *              },
     *              "59" : {
     *                  "gid": "59",
     *                  "groupOrder": "2",
     *                  "questions": [
     *                      {
     *                      "qid": "726",
     *                      "gid": "59",
     *                      "questionOrder": "1"
     *                      },
     *                      {
     *                      "qid": "727",
     *                      "gid": "59",
     *                      "questionOrder": "2"
     *                      }
     *                  ]
     *              }
     *          }
     *      }
     * ]
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
        foreach ($op->getProps() as $i => $groupData) {
            $tfGroupData = $this->transformerGroup->transform($groupData);
            $this->checkGroupReorderData($op, $tfGroupData, 'group');
            $groupReorderData[$i] = $tfGroupData;
            foreach ($groupData['questions'] as $k => $questionData) {
                $tfQuestionData = $this->transformerQuestion->transform(
                    $questionData
                );
                $this->checkGroupReorderData($op, $tfQuestionData, 'question');
                $groupReorderData[$i]['questions'][$k] = $tfQuestionData;
            }
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
                printf(
                    'No values to update for entity "%s"',
                    $op->getEntityType()
                )
            );
        }
        foreach ($required as $param) {
            if (!array_key_exists($param, $data)) {
                throw new OpHandlerException(
                    printf(
                        'Required parameter "%s" is missing. Entity "%s"',
                        $param,
                        $op->getEntityType()
                    ) . print_r($data, true)
                );
            }
        }
    }
}
