<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputThemeSettings;
use LimeSurvey\Models\Services\SurveyThemeConfiguration;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerExceptionTrait,
    OpHandlerValidationTrait
};

class OpHandlerThemeSettings implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerValidationTrait;

    protected TransformerInputThemeSettings $transformer;

    public function __construct(TransformerInputThemeSettings $transformer)
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
        $isThemeSettings = $op->getEntityType() === 'themeSettings';

        return $isUpdateOperation && $isThemeSettings;
    }

    /**
     * Handle themeSetting update.
     *
     *   Expects a patch structure like this:
     *   {
     *        "id": 571271,
     *        "op": "update",
     *        "entity": "themeSettings",
     *        "error": false,
     *        "props": {
     *            "showclearall": "on"
     *        }
     *   }

     *
     * @param OpInterface $op
     * @return void
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyThemeConfigurationService = $diContainer->get(
            SurveyThemeConfiguration::class
        );

        $surveyId = $this->getSurveyIdFromContext($op);
        $aProps = $op->getProps();
        $readyProps = $this->transformer->transform(
            $aProps,
            ['surveyID' => $surveyId]
        );

        $surveyThemeConfigurationService->updateThemeOption($op->getEntityId(), $readyProps);

        return;
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        if (empty($validationData)) {
            $validationData = $this->transformer->validate(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
            $validationData = $this->validateEntityId(
                $op,
                !is_array($validationData) ? [] : $validationData
            );
        }

        return $this->getValidationReturn(
            gT('Could not save theme settings'),
            $validationData,
            $op
        );
    }
}
