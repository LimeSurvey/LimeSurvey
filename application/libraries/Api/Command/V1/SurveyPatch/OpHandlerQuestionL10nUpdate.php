<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerExceptionTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Models\Services\{
    QuestionAggregateService,
    QuestionAggregateService\L10nService,
    QuestionAggregateService\QuestionService,
    Exception\PermissionDeniedException,
    Exception\NotFoundException,
    Exception\PersistErrorException
};
use LimeSurvey\ObjectPatch\{Op\OpInterface,
    OpHandler\OpHandlerException,
    OpType\OpTypeUpdate,
    OpHandler\OpHandlerInterface
};

class OpHandlerQuestionL10nUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerValidationTrait;

    protected L10nService $l10nService;
    protected TransformerInputQuestionL10ns $transformer;
    protected QuestionAggregateService $questionAggregateService;
    protected QuestionService $questionService;

    /**
     * @param L10nService $l10nService
     * @param TransformerInputQuestionL10ns $transformer
     * @param QuestionAggregateService $questionAggregateService
     * @param QuestionService $questionService
     */
    public function __construct(
        L10nService $l10nService,
        TransformerInputQuestionL10ns $transformer,
        QuestionAggregateService $questionAggregateService,
        QuestionService $questionService
    ) {
        $this->l10nService = $l10nService;
        $this->transformer = $transformer;
        $this->questionAggregateService = $questionAggregateService;
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
            && $op->getEntityType() === 'questionL10n';
    }

    /**
     * Handle questionL10n update operation.
     *
     * Expects a patch structure like this:
     * {
     *     "entity": "questionL10n",
     *     "op": "update",
     *     "id": 12345, // qid of the question !!!
     *     "props": {
     *         "en": {
     *             "question": "Array Question",
     *             "help": "Help text"
     *         },
     *         "de": {
     *             "question": "Array ger",
     *             "help": "help ger"
     *         }
     *     }
     * }
     *
     * @param OpInterface $op
     * @return void
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws OpHandlerException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        // Permission was checked against the context survey, so the
        // question must belong to it.
        $question = $this->questionService->getQuestionBySidAndQid(
            $surveyId,
            (int)$op->getEntityId()
        );
        $transformedProps = $this->transformer->transformAll(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }

        $this->l10nService->save(
            $question->qid,
            $transformedProps
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        $validationData = $this->validateCollectionIndex($op, $validationData);
        $validationData = $this->validateEntityId($op, $validationData);
        if (empty($validationData)) {
            $validationData = $this->transformer->validateAll(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }

        return $this->getValidationReturn(
            gT('Could not save question'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
