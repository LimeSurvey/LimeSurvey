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
    OpType\OpTypeUpdate};
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
     * All existing answers are needed independently from "update" or "create".
     * {
     *     "patch": [{
     *             "entity": "answer",
     *             "op": "create", // "update"
     *             "id": "726", // qid(!)
     *             "props": {
     *                 "0": {
     *                     "code": "XX01",
     *                     "sortOrder": 0,
     *                     "assessmentValue": 0,
     *                     "scaleId": 0,
     *                     "l10ns": {
     *                         "de": {
     *                             "answer": "antwort1",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "answer": "answer1",
     *                             "language": "en"
     *                         }
     *                     }
     *                 },
     *                 "1": {
     *                     "code": "YY02",
     *                     "sortOrder": 1,
     *                     "assessmentValue": 0,
     *                     "scaleId": 0,
     *                     "l10ns": {
     *                         "de": {
     *                             "answer": "antwort1.2",
     *                             "language": "de"
     *                         },
     *                         "en": {
     *                             "answer": "answer1.2",
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
        if ($question) {
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
    }
}
