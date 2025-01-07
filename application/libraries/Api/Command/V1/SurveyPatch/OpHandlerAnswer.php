<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerQuestionTrait,
    OpHandlerExceptionTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpType\OpTypeCreate,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};
use LimeSurvey\Models\Services\{
    QuestionAggregateService,
    QuestionAggregateService\QuestionService,
    QuestionAggregateService\AnswersService
};

class OpHandlerAnswer implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerQuestionTrait;
    use OpHandlerValidationTrait;

    protected string $entity;
    protected TransformerInputAnswer $transformer;
    protected AnswersService $answersService;
    protected QuestionService $questionService;
    protected QuestionAggregateService $questionAggregateService;

    public function __construct(
        TransformerInputAnswer $transformer,
        AnswersService $answersService,
        QuestionService $questionService,
        QuestionAggregateService $questionAggregateService
    ) {
        $this->entity = 'answer';
        $this->transformer = $transformer;
        $this->answersService = $answersService;
        $this->questionService = $questionService;
        $this->questionAggregateService = $questionAggregateService;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isCreateOperation = $op->getType()->getId() === OpTypeCreate::ID;
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isAnswerEntity = $op->getEntityType() === $this->entity;

        return ($isCreateOperation || $isUpdateOperation) && $isAnswerEntity;
    }

    /**
     *
     * Example patch:
     * "id" is the qid, so we are only allowing answers for a single question in
     * the patch.
     * Attention: Currently all answers not provided in the patch
     *            will be deleted by the service. Doesn't matter if
     *            create or update was chosen
     *
     * Example for "update":
     * {
     *     "patch": [{
     *             "entity": "answer",
     *             "op": "update",
     *             "id": "809",
     *             "props": {
     *                 "0": {
     *                     "aid": 465,
     *                     "code": "AO01",
     *                     "sortOrder": 2,
     *                     "assessmentValue": 0,
     *                     "scaleId": 1,
     *                     "l10ns": {
     *                         "de": {
     *                             "answer": "ANTW1 scale 1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "answer": "ANS1 scale 1",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "aid": 467,
     *                     "code": "AO02",
     *                     "sortOrder": 3,
     *                     "assessmentValue": 0,
     *                     "scaleId": 1,
     *                     "l10ns": {
     *                         "de": {
     *                             "answer": "ANTW2 scale 1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "answer": "ANS2 scale 1",
     *                             "language": "en"
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     *
     * Example for "create":
     * {
     *     "patch": [{
     *             "entity": "answer",
     *             "op": "create",
     *             "id": "809",
     *             "props": {
     *                 "0": {
     *                     "tempId": "222",
     *                     "code": "AO11",
     *                     "sortOrder": 4,
     *                     "assessmentValue": 0,
     *                     "scaleId": 1,
     *                     "l10ns": {
     *                         "de": {
     *                             "answer": "ANTW11 scale 1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "answer": "ANS11 scale 1",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "tempId": "223",
     *                     "code": "AO12",
     *                     "sortOrder": 5,
     *                     "assessmentValue": 0,
     *                     "scaleId": 1,
     *                     "l10ns": {
     *                         "de": {
     *                             "answer": "ANTW12 scale 1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "answer": "ANS12 scale 1",
     *                             "language": "en"
     *                         }
     *                     }
     *                 }
     *             }
     *         }
     *     ]
     * }
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     */
    public function handle(OpInterface $op): array
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $question = $this->questionService->getQuestionBySidAndQid(
            $surveyId,
            $op->getEntityId()
        );

        $data = $this->transformer->transformAll(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        $this->answersService->save(
            $question,
            $data
        );

        return [
            'tempIdMapping' => $this->getSubQuestionNewIdMapping(
                $question,
                $data,
                true
            )
        ];
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        $validationData = $this->validateCollectionIndex($op, $validationData, false);
        if (empty($validationData)) {
            $validationData = $this->transformer->validateAll(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
            $validationData = $this->validateEntityId(
                $op,
                !is_array($validationData) ? [] : $validationData
            );
        }

        return $this->getValidationReturn(
            gT('Could not save answer option'),
            $validationData,
            $op
        );
    }
}
