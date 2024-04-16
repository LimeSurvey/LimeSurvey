<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use DI\DependencyException;
use DI\NotFoundException;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Transformer\TransformerException;
use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\Models\Services\{
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    SurveyAggregateService
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerSurveyUpdate implements OpHandlerInterface
{
    use OpHandlerExceptionTrait;
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

    protected string $entity;
    protected Survey $model;
    protected TransformerInputSurvey $transformer;

    public function __construct(
        Survey $model,
        TransformerInputSurvey $transformer
    ) {
        $this->entity = 'survey';
        $this->model = $model;
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
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isSurveyEntity = $op->getEntityType() === 'survey';

        return $isUpdateOperation && $isSurveyEntity;
    }

    /**
     * Saves the changes to the database.
     * NOTE: when we update the language,
     *       additionalLanguages need also to be added in the props,
     *       or they will be deleted.
     * Expects this structure, note that the entity id is not required.
     * as the survey id will be in the context:
     * {
     *       "patch": [{
     *       "entity": "survey",
     *       "op": "update",
     *       "props": {
     *         "anonymized": false,
     *         "language": "en",
     *         "additionalLanguages":["de"],
     *         "expires": "2001-03-20 13:28:00",
     *         "template": "fruity_twentythree",
     *         "format": "G"
     *       }
     *     }
     *   ]
     * }
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     * @throws PersistErrorException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws TransformerException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyUpdater = $diContainer->get(
            SurveyAggregateService::class
        );
        $surveyId = $this->getSurveyIdFromContext($op);
        $surveyUpdater->checkSurveySettingsUpdatePermission($surveyId);
        $surveyUpdater->setRestMode(true);

        $props = $op->getProps();
        $transformedProps = $this->transformer->transform($props);

        if ($props === null || $transformedProps === null) {
            $this->throwNoValuesException($op);
        }
        /** @var array $transformedProps */
        $surveyUpdater->update(
            $surveyId,
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
        if (empty($validationData)) {
            $validationData = $this->transformer->validate(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }

        return $this->getValidationReturn(
            gT('Could not save survey'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
