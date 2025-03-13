<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerQuestionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputSubQuestion
};
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    QuestionAggregateService,
    QuestionAggregateService\QuestionService,
    QuestionAggregateService\SubQuestionsService
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
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
    use OpHandlerValidationTrait;

    protected QuestionAggregateService $questionAggregateService;
    protected SubQuestionsService $subQuestionsService;
    protected QuestionService $questionService;
    protected TransformerInputSubQuestion $transformer;

    public function __construct(
        QuestionAggregateService $questionAggregateService,
        SubQuestionsService $subQuestionsService,
        QuestionService $questionService,
        TransformerInputSubQuestion $transformer
    ) {
        $this->questionAggregateService = $questionAggregateService;
        $this->subQuestionsService = $subQuestionsService;
        $this->questionService = $questionService;
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
        return (
                $op->getType()->getId() === OpTypeUpdate::ID
                || $op->getType()->getId() === OpTypeCreate::ID
            )
            && $op->getEntityType() === 'subquestion';
    }

    /**
     * Handle subquestion create or update operation.
     * Attention: subquestions not present in the patch will be deleted.
     * Expects a patch structure like this for update:
     * {
     *     "patch": [{
     *             "entity": "subquestion",
     *             "op": "update",
     *             "id": 722, //parent qid
     *             "props": {
     *                 "0": {
     *                     "qid": 728,
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
     * Expects a patch structure like this for create:
     * {
     *     "patch": [{
     *             "entity": "subquestion",
     *             "op": "create",
     *             "id": 722,
     *             "props": {
     *                 "0": {
     *                     "tempId": "456789",
     *                     "title": "SQ011",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "germanized1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "englishized",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "title": "SQ012",
     *                     "tempId": "345678",
     *                     "l10ns": {
     *                         "de": {
     *                             "question": "germanized2",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "question": "englishized2",
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
     * @return array
     * @throws NotFoundException
     * @throws OpHandlerException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): array
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $transformOptions = ['operation' => $op->getType()->getId()];
        $data = $this->transformer->transformAll(
            $op->getProps(),
            $transformOptions
        );
        $questionId = $op->getEntityId();
        $question = $this->questionService->getQuestionBySidAndQid(
            $surveyId,
            $questionId
        );
        $this->subQuestionsService->save(
            $question,
            $data,
            true
        );
        $mapping = $this->getSubQuestionNewIdMapping(
            $question,
            $data
        );

        return !empty($mapping) ? [
            'tempIdMapping' => $mapping
        ] : [];
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
        $validationData = $this->validateEntityId($op, $validationData);
        if (empty($validationData)) {
            $validationData = $this->transformer->validateAll(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }

        return $this->getValidationReturn(
            gT('Could not save subquestions'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
