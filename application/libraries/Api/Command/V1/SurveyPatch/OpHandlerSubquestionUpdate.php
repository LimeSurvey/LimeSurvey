<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerSubquestionUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected QuestionAggregateService $questionAggregateService;
    protected TransformerInputQuestion $transformer;

    public function __construct(
        QuestionAggregateService $questionAggregateService,
        TransformerInputQuestion $transformer
    ) {
        $this->questionAggregateService = $questionAggregateService;
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
            && $op->getEntityType() === 'subquestion';
    }

    /**
     * Handle subquestion update operation.
     * Attention: subquestions not present in the patch will be deleted.
     * Expects a patch structure like this:
     * {
     *     "patch": [{
     *             "entity": "subquestion",
     *             "op": "update",
     *             "id": "0", //not relevant at all
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
     * @param OpInterface $op
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op): void
    {
        $this->questionAggregateService->save(
            $this->getSurveyIdFromContext($op),
            $this->getPreparedData($op)
        );
    }

    /**
     * Organizes the patch data into the structure which
     * is expected by the service.
     * @param OpInterface $op
     * @return array
     */
    public function getPreparedData(OpInterface $op): array
    {
        $props = $op->getProps();
        $transformedProps = $this->transformer->transform($props);

        if ($props === null || $transformedProps === null) {
            throw new OpHandlerException(
                sprintf(
                    'No values to update for entity %s',
                    $op->getEntityType()
                )
            );
        }
        if (
            !array_key_exists(
                'qid',
                $transformedProps
            )
            || $transformedProps['qid'] === null
        ) {
            $transformedProps['qid'] = $op->getEntityId();
        }

        return ['question' => $transformedProps];
    }
}
