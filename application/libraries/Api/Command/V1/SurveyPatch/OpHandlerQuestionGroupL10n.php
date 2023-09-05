<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

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
     *      "id": {
     *          "gid": 50,
     *          "language": "de"
     *      },
     *      "props": {
     *          "groupName": "Gruppenname",
     *          "description": "Deutsche Beschreibung"
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
            $this->getQuestionGroupId($op)
        );

        $questionGroupService->updateQuestionGroupLanguages(
            $questionGroup,
            $this->getDataArray($op)
        );
    }

    /**
     * @param OpInterface $op
     * @return mixed
     * @throws OpHandlerException
     */
    private function getQuestionGroupId(OpInterface $op)
    {
        if (
            is_array($op->getEntityId())
            && array_key_exists(
                'gid',
                $op->getEntityId()
            )
        ) {
            $transformedIdArray = $this->transformer->transform(
                $op->getEntityId()
            );
            if (
                is_array($transformedIdArray)
                && array_key_exists(
                    'gid',
                    $transformedIdArray
                )
            ) {
                return $transformedIdArray['gid'];
            }
        } else {
            throw new OpHandlerException('no gid provided');
        }
    }

    /**
     * Builds and returns the data array for the update operation as the
     * service class expects it.
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     */
    public function getDataArray(OpInterface $op)
    {
        $dataSet = [];
        $entityIdArray = $op->getEntityId();
        if (
            is_array($entityIdArray)
            && array_key_exists(
                'language',
                $entityIdArray
            )
        ) {
            $transformedProps = $this->transformer->transform($op->getProps());
            if ($transformedProps == null) {
                throw new OpHandlerException(
                    sprintf(
                        'no transformable props provided for %s with id "%s"',
                        $this->entity,
                        print_r($op->getEntityId(), true)
                    )
                );
            }
            foreach ($transformedProps as $key => $value) {
                $dataSet[$entityIdArray['language']][$key] = $value;
            }
        } else {
            throw new OpHandlerException(
                sprintf(
                    'no language parameter provided within id parameter for %s with id "%s"',
                    $this->entity,
                    print_r($op->getEntityId(), true)
                )
            );
        }
        return $dataSet;
    }
}
