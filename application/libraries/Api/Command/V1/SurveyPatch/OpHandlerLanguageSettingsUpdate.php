<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use DI\DependencyException;
use DI\NotFoundException;
use SurveyLanguageSetting;
use LimeSurvey\Api\Command\V1\Transformer\{
    Input\TransformerInputSurveyLanguageSettings,
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\OpHandlerSurveyTrait;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\{
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    SurveyAggregateService\LanguageSettings
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerLanguageSettingsUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected string $entity;
    protected SurveyLanguageSetting $model;
    protected TransformerInterface $transformer;

    public function __construct(
        SurveyLanguageSetting $model,
        TransformerInputSurveyLanguageSettings $transformer
    ) {
        $this->entity = 'languageSetting';
        $this->model = $model;
        $this->transformer = $transformer;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isLanguageSettingEntity = $op->getEntityType() === 'languageSetting';

        return $isUpdateOperation && $isLanguageSettingEntity;
    }

    /**
     * This handler accepts two different approaches to update the language settings:
     * Approach 1 (single language):
     * - in this case the language needs to be part of the id array
     * - patch structure:
     *      {
     *          "entity": "languageSetting",
     *          "op": "update",
     *          "id": {
     *              "sid": 123456,  //todo do we still need the sid here?
     *              "language": "de"
     *          },
     *          "props": {
     *              "title": "Beispielfragebogen"
     *          }
     *      }
     *
     * Approach 2 (multiple languages):
     * - in this case the languages need to be indexes of the props array
     * - language must not be part of the id array
     * - patch structure:
     *      {
     *          "entity": "languageSetting",
     *          "op": "update",
     *          "id": {
     *              "sid": 123456  //todo do we still need the sid here?
     *          },
     *          "props": {
     *              "de": {
     *                  "title": "Beispielfragebogen"
     *              },
     *              "en": {
     *                  "title": "Example Survey"
     *              }
     *          }
     *      }
     * @param OpInterface $op
     * @return void
     * @throws OpHandlerException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $languageSettings = $diContainer->get(
            LanguageSettings::class
        );

        $languageSettings->update(
            $this->getSurveyIdFromContext($op),
            $this->getLanguageSettingsData($op)
        );
    }

    /**
     * Analyzes the patch, builds and returns the correct data structure
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     */
    public function getLanguageSettingsData(OpInterface $op)
    {
        $data = [];
        $entityId = $op->getEntityId();
        if (is_array($entityId) && array_key_exists('language', $entityId)) {
            // indicator for variant 1
            $data[$entityId['language']] = $this->getTransformedProps(
                $op,
                $op->getProps()
            );
        } else {
            // variant 2
            foreach ($op->getProps() as $language => $props) {
                $data[$language] = $this->getTransformedProps($op, $props);
            }
        }

        return $data;
    }

    /**
     * @param OpInterface $op
     * @param array|null $props
     * @return mixed
     * @throws OpHandlerException
     */
    private function getTransformedProps(OpInterface $op, ?array $props)
    {
        $transformedProps = $this->transformer->transform($props);
        if ($props === null || $transformedProps === null) {
            $this->throwNoValuesException($op);
        }
        return $transformedProps;
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // getTransformedProps will throw an exception if the patch is not valid
        return true;
    }
}
