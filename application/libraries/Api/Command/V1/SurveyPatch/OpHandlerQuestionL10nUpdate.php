<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerSurveyTrait;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Models\Services\{Exception\NotFoundException,
    QuestionAggregateService,
    Exception\PersistErrorException
};
use LimeSurvey\ObjectPatch\{Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerQuestionL10nUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected QuestionAggregateService\L10nService $l10nService;
    protected TransformerInputQuestionL10ns $transformer;

    public function __construct(
        QuestionAggregateService\L10nService $l10nService,
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
            (int)$op->getEntityId(),
            $this->getTransformedLanguageProps(
                $op,
                $this->transformer,
                'questionL10n'
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
        //getTransformedLanguageProps already checks if the patch is valid
        return true;
    }
}
