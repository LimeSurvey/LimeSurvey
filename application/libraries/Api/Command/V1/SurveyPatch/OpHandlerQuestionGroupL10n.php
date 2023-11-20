<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerSurveyTrait;
use QuestionGroupL10n;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerQuestionGroupL10n implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected string $entity;
    protected QuestionGroupL10n $model;
    protected TransformerInputQuestionGroupL10ns $transformer;

    public function __construct(
        QuestionGroupL10n $model,
        TransformerInputQuestionGroupL10ns $transformer
    ) {
        $this->entity = 'questionGroupL10n';
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
        return $op->getType()->getId() === OpTypeUpdate::ID
            && $op->getEntityType() === $this->entity;
    }

    /**
     * Saves the changes to the database.
     * Expects a patch structure like this:
     * {
     *      "entity": "questionGroupL10n",
     *      "op": "update",
     *      "id": 1,
     *      "props": {
     *          en": {
     *              "groupName": "Name of group",
     *              "description": "English description"
     *          },
     *          de": {
     *              "groupName": "Gruppenname",
     *              "description": "Deutsche Beschreibung"
     *          }
     *      }
     * }
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionGroupService = $diContainer->get(
            QuestionGroupService::class
        );

        $questionGroup = $questionGroupService->getQuestionGroupForUpdate(
            $this->getSurveyIdFromContext($op),
            $op->getEntityId()
        );

        $questionGroupService->updateQuestionGroupLanguages(
            $questionGroup,
            $this->getTransformedLanguageProps(
                $op,
                $this->transformer,
                $this->entity
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
        //the function getTransformedLanguageProps checks if the patch is valid
        //it is already used in the handle() method ...
        return true;
    }
}
