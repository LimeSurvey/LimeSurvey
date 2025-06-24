<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\Models\Services\SurveyAggregateService;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use Survey;

class OpHandlerSurveyStatus implements OpHandlerInterface
{
    use OpHandlerExceptionTrait;
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

    protected TransformerInputSurvey $transformer;

    protected string $action = '';

    public function __construct(TransformerInputSurvey $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isSurveyStatus = $op->getEntityType() === 'surveyStatus';
        $props = $op->getProps();

        foreach (['activate', 'deactivate', 'expire'] as $actionCandidate) {
            if ($props[$actionCandidate] ?? false) {
                $this->action = $actionCandidate;
            }
        }

        return $isUpdateOperation && $isSurveyStatus && (!!$this->action);
    }

    /**
     * Handle subquestion delete operation.
     *
     *   Expects a patch structure like this:
     *   {
     *        "id": 571271,
     *        "op": "update",
     *        "entity": "surveyActivate",
     *        "error": false,
     *        "props": {
     *            "anonymized": false
     *        }
     *   }

     *
     * @param OpInterface $op
     * @return array
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyActivateService = $diContainer->get(
            SurveyAggregateService::class
        );
        $props = $op->getProps();
        if (!isset($props['ok'])) {
            $props['ok'] = true;
        }
        $surveyActivateService->{$this->action}($op->getEntityId(), $props);
        $return = [];
        $survey = Survey::model()->findByPk($op->getEntityId());
        if (($this->action === 'expire') && $survey && $survey->expires) {
            $return['additional'] = [
                'expire' => $survey->expires
            ];
        }
        return $return;
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->transformer->validate(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );

        return $this->getValidationReturn(
            gT('Could not save survey'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
