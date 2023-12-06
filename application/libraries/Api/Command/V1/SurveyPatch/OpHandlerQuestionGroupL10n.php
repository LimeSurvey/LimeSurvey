<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerExceptionTrait;
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
    use OpHandlerExceptionTrait;

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
     *          "en": {
     *              "groupName": "Name of group",
     *              "description": "English description"
     *          },
     *          "de": {
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
        $data = $this->transformAllLanguageProps(
            $op,
            $op->getProps(),
            $this->entity
        );

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
            $data
        );
    }

    /**
     * Transforms language related props to array
     * This function is shared by QuestionL10n and QuestionGroupL10n operations,
     * so the actual transformer needs to
     * @param OpInterface $op
     * @param array $props
     * @param string $entity
     * @return array
     * @throws OpHandlerException
     */
    private function transformAllLanguageProps(
        OpInterface $op,
        array $props,
        string $entity
    ): array {
        $dataSet = [];
        foreach ($props as $language => $properties) {
            if (is_numeric($language)) {
                throw new OpHandlerException(
                    sprintf(
                        'no indexes for language provided within props for %s with id "%s"',
                        $entity,
                        print_r($op->getEntityId(), true)
                    )
                );
            }
            if (empty($properties)) {
                throw new OpHandlerException(
                    sprintf(
                        'no props provided for %s with id "%s"',
                        $entity,
                        print_r($op->getEntityId(), true)
                    )
                );
            }
            $errors = $this->transformer->validate($properties);
            if (is_array($errors)) {
                throw new OpHandlerException(
                    sprintf(
                        'failed to transform  %s with id "%s": %s',
                        $entity,
                        print_r($op->getEntityId(), true),
                        $errors[0]
                    )
                );
            }

            $transformedProps = $this->transformer->transform($properties);
            $dataSet[$language] = $transformedProps;
        }
        return $dataSet;
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
