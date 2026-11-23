<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    QuestionAggregateService,
    QuestionAggregateService\QuestionService,
    SurveyCondition
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{OpHandlerSurveyTrait,
    OpHandlerExceptionTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerQuestionUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerValidationTrait;

    protected QuestionAggregateService $questionAggregateService;
    protected TransformerInputQuestion $transformer;
    protected SurveyCondition $surveyCondition;
    protected QuestionService $questionService;

    /**
     * @param QuestionAggregateService $questionAggregateService The service that saves the question
     * @param TransformerInputQuestion $transformer The transformer of the patch props
     * @param SurveyCondition $surveyCondition The service that reads the conditions of a question
     * @param QuestionService $questionService The question service used by the save: It knows the questions whose conditions the save deleted
     * @return void
     */
    public function __construct(
        QuestionAggregateService $questionAggregateService,
        TransformerInputQuestion $transformer,
        SurveyCondition $surveyCondition,
        QuestionService $questionService
    ) {
        $this->questionAggregateService = $questionAggregateService;
        $this->transformer = $transformer;
        $this->surveyCondition = $surveyCondition;
        $this->questionService = $questionService;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeUpdate::ID
            && $op->getEntityType() === 'question';
    }

    /**
     * Handle question update operation.
     *
     * Expects a patch structure like this:
     * {
     *     "entity": "question",
     *     "op": "update",
     *     "id": 1,
     *     "props": {
     *         "title": "Q03",
     *         "mandatory": true,
     *         "encrypted": true
     *     }
     * }
     *
     * An incompatible type change deletes the conditions of other questions on
     * this question (on its answer options or its "Other"): The response then
     * returns their relevance and conditions, so the editor shows the change
     * without a reload.
     *
     * @param OpInterface $op The question update operation
     * @return array The response, with the questions whose conditions changed in "additional"
     * @throws OpHandlerException
     * @throws TransformerException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): array
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $transformedProps = $this->transformer->transform(
            $op->getProps(),
            [
                'operation' => $op->getType()->getId(),
                'id' => $op->getEntityId()
            ]
        );
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }
        $this->questionAggregateService->save(
            $surveyId,
            [
                'question' => $transformedProps,
                'loadCurrentAdvancedSettings' => true
            ]
        );
        // Only an incompatible type change deletes conditions of other questions
        $dependentQuestionIds = $this->questionService->getChangedDependentQuestionIds();
        if (empty($dependentQuestionIds)) {
            return [];
        }
        $relevances = \Yii::app()->db->createCommand()
            ->select('qid, relevance')
            ->from('{{questions}}')
            ->where(['in', 'qid', $dependentQuestionIds])
            ->queryAll();
        $scenarios = $this->surveyCondition->getScenariosAndConditionsOfQuestions($dependentQuestionIds);
        $dependentQuestions = [];
        foreach ($relevances as $row) {
            $dependentQuestions[] = [
                'qid' => (int) $row['qid'],
                'relevance' => $row['relevance'] ?? '1',
                'scenarios' => $scenarios[(int) $row['qid']] ?? [],
            ];
        }
        return ['additional' => $dependentQuestions];
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->transformer->validate(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        $validationData = $this->validateEntityId(
            $op,
            !is_array($validationData) ? [] : $validationData
        );
        $validationData = $this->validateSurveyIdFromContext(
            $op,
            $validationData
        );

        return $this->getValidationReturn(
            gT('Could not save question'),
            $validationData,
            $op
        );
    }
}
