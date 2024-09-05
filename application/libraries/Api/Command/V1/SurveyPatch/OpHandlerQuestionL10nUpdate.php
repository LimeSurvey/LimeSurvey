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

    public function __construct(
        L10nService $l10nService,
        TransformerInputQuestionL10ns $transformer,
        QuestionAggregateService $questionAggregateService
    ) {
        $this->l10nService = $l10nService;
        $this->transformer = $transformer;
        $this->questionAggregateService = $questionAggregateService;
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
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws OpHandlerException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op): void
    {
        $this->questionAggregateService->checkUpdatePermission(
            $this->getSurveyIdFromContext($op)
        );
        $transformedProps = $this->transformer->transformAll(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }

        $this->l10nService->save(
            (int)$op->getEntityId(),
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
