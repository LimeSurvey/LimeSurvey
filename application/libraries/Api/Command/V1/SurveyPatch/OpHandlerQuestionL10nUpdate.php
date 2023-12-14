<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerExceptionTrait
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Models\Services\{
    QuestionAggregateService\L10nService,
    Exception\NotFoundException,
    Exception\PersistErrorException
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpType\OpTypeUpdate,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface
};

class OpHandlerQuestionL10nUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;

    protected L10nService $l10nService;
    protected TransformerInputQuestionL10ns $transformer;

    public function __construct(
        L10nService $l10nService,
        TransformerInputQuestionL10ns $transformer
    ) {
        $this->l10nService = $l10nService;
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
     * @throws OpHandlerException
     * @throws PersistErrorException
     * @throws NotFoundException
     */
    public function handle(OpInterface $op): void
    {
        $this->l10nService->save(
            (int) $op->getEntityId(),
            $this->transformAll($op)
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        //transformAllLanguageProps  already checks if the patch is valid
        return true;
    }


    /**
     * Transforms language related props to array
     * This function is shared by QuestionL10n and QuestionGroupL10n operations,
     * so the actual transformer needs to
     * @param OpInterface $op
     * @param array $props
     * @return array
     * @throws OpHandlerException
     */
    private function transformAll(OpInterface $op): array {
        $transformOptions = ['operation' => $op->getType()->getId()];
        $this->throwTransformerValidationErrors(
            $this->transformer->validate(
                $op->getProps(),
                $transformOptions
            ),
            $op
        );
        return $this->transformer->transformAll(
            $op->getProps(),
            $transformOptions
        );
    }
}
