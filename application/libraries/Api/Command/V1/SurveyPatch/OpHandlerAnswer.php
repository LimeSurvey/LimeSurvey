<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\{
    Input\TransformerInputAnswer,
    Input\TransformerInputAnswerL10ns,
};
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\ObjectPatch\{Op\OpInterface,
    OpType\OpTypeCreate,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};
use LimeSurvey\Models\Services\QuestionAggregateService\AnswersService;

class OpHandlerAnswer implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerQuestionTrait;

    protected string $entity;
    protected TransformerInputAnswer $transformerAnswer;
    protected TransformerInputAnswerL10ns $transformerAnswerL10n;
    protected AnswersService $answersService;
    protected QuestionService $questionService;

    public function __construct(
        TransformerInputAnswer $transformerAnswer,
        TransformerInputAnswerL10ns $transformerAnswerL10n,
        AnswersService $answersService,
        QuestionService $questionService
    ) {
        $this->entity = 'answer';
        $this->transformerAnswer = $transformerAnswer;
        $this->transformerAnswerL10n = $transformerAnswerL10n;
        $this->answersService = $answersService;
        $this->questionService = $questionService;
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
     *            will be deleted by the service.
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
     * @param OpInterface $op
     * @return void
     * @throws OpHandlerException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\Models\Services\Exception\PersistErrorException
     */
    public function handle(OpInterface $op): void
    {
        $question = $this->questionService->getQuestionBySidAndQid(
            $this->getSurveyIdFromContext($op),
            $op->getEntityId()
        );
        $this->answersService->save(
            $question,
            $this->prepareAnswers(
                $op,
                $op->getProps(),
                $this->transformerAnswer,
                $this->transformerAnswerL10n,
                ['answer', 'answerL10n']
            )
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // TODO: Implement isValidPatch() method.
        return true;
    }
}
