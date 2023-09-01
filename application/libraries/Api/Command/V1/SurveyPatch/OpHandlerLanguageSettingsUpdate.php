<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyPatch;

use CModel;
use DI\DependencyException;
use DI\NotFoundException;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyTrait;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\SurveyUpdater\LanguageSettings;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;

class OpHandlerLanguageSettingsUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected ?TransformerInterface $transformer = null;
    protected string $entity;
    protected CModel $model;

    public function __construct(
        string $entity,
        CModel $model,
        TransformerInterface $transformer = null
    ) {
        $this->entity = $entity;
        $this->model = $model;
        $this->transformer = $transformer;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isSurveyEntity = $op->getEntityType() === 'languageSetting';

        return $isUpdateOperation && $isSurveyEntity;
    }

    /**
     * This handler accepts two different approaches to update the language settings:
     * Approach 1 (single language):
     *  - in this case the language needs to be part of the id array
     * - patch structure:
     *      {
     *          "entity": "languageSetting",
     *          "op": "update",
     *          "id": {
     *              "sid": 123456,
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
     *              "sid": 123456
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
            $this->getLanguageSettingData($op)
        );
    }

    /**
     * Analyzes the patch, builds and returns the correct data structure
     * @param OpInterface $op
     * @return array
     * @throws OpHandlerException
     */
    public function getLanguageSettingData(OpInterface $op)
    {
        $data = [];
        $entityId = $op->getEntityId();
        if (is_array($entityId) && array_key_exists('language', $entityId)) {
            // indicator for variant 1
            $data[$entityId['language']] = $this->getTransformedProps($op, $op->getProps());
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
            throw new OpHandlerException(printf(
                'No values to update for entity %s',
                $op->getEntityType()
            ));
        }
        return $transformedProps;
    }
}
