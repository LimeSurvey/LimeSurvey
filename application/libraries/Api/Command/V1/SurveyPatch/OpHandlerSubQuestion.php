<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputSubQuestion,
    TransformerInputQuestionL10ns
};
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    QuestionAggregateService,
    QuestionAggregateService\QuestionService,
    QuestionAggregateService\SubQuestionsService
};
use LimeSurvey\ObjectPatch\{Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeCreate,
    OpType\OpTypeUpdate
};

/**
 * Class OpHandlerSubQuestion can handle create and update
 * of subquestions which belong to a single question.
 */
class OpHandlerSubQuestion implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerQuestionTrait;

    protected QuestionAggregateService $questionAggregateService;
    protected SubQuestionsService $subQuestionsService;
    protected QuestionService $questionService;
    protected TransformerInputSubQuestion $transformer;
    protected TransformerInputQuestionL10ns $transformerL10ns;

    public function __construct(
        QuestionAggregateService $questionAggregateService,
        SubQuestionsService $subQuestionsService,
        QuestionService $questionService,
        TransformerInputQuestionL10ns $transformerL10n,
        TransformerInputSubQuestion $transformer
    ) {
        $this->questionAggregateService = $questionAggregateService;
        $this->subQuestionsService = $subQuestionsService;
        $this->questionService = $questionService;
        $this->transformer = $transformer;
        $this->transformerL10ns = $transformerL10n;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return (
                $op->getType()->getId() === OpTypeUpdate::ID
                || $op->getType()->getId() === OpTypeCreate::ID
            )
            && $op->getEntityType() === 'subquestion';
    }

    /**
     * Handle subquestion create or update operation.
     * Attention: subquestions not present in the patch will be deleted.
     * For a proper update the props should contain "oldCode"
     * additional to the "title". If "oldCode" is not provided,
     * the SubQuestionService will do a create operation.
     * Expects a patch structure like this:
     * {
     *     "patch": [{
     *             "entity": "subquestion",
     *             "op": "update",
     *             "id": 722, //parent qid
     *             "props": {
     *                 "0": {
     *                     "qid": 728,
     *                     "oldCode": "SQ001",
     *                     "title": "SQ001new",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "subger1updated",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "sub1updated",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "qid": 729,
     *                     "oldCode": "SQ002",
     *                     "title": "SQ002new",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "subger2updated",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "sub2updated",
     *                             "language": "en"
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $preparedData = $this->prepareSubQuestions(
            $op,
            $this->transformer,
            $this->transformerL10ns,
            $op->getProps(),
            ['subquestions']
        );
        $questionId = $op->getEntityId();
        $this->subQuestionsService->save(
            $this->questionService->getQuestionBySidAndQid(
                $surveyId,
                $questionId
            ),
            $preparedData
        );
    }
}
