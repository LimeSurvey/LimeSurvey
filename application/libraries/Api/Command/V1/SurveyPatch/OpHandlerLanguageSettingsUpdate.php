<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyPatch;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;

class OpHandlerLanguageSettingsUpdate implements OpHandlerInterface
{

    protected ?TransformerInterface $transformer = null;
    protected string $entity;
    protected CModel $model;

    public function __construct(string $entity, CModel $model, TransformerInterface $transformer = null)
    {
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

    public function handle(OpInterface $op): void
    {
        $props = $op ->getProps();
        if ($props === null) {
            throw new OpHandlerException(
                printf(
                    'No values to update for entity %s',
                    $op->getEntityType()
                )
            );
        }
        //id is surveyid + language   (e.g. language en)
        $entityID = $op->getEntityId();
        $languageSetting = \SurveyLanguageSetting::model()->findByAttributes(
            [
                'surveyls_survey_id' => $entityID['sid'],
                'surveyls_language' => $entityID['language']
            ]
        );
        $languageSetting->setAttributes($props);

        if (!$languageSetting->save()) {
            throw new OpHandlerException(
                sprintf(
                    'Could not update language setting for entity %s',
                    $op->getEntityType()
                )
            );
        }
    }
}
