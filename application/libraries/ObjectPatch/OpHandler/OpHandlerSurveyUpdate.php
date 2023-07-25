<?php

namespace LimeSurvey\ObjectPatch\OpHandler;

use LimeSurvey\ObjectPatch\Op\OpInterface;
//use LimeSurvey\Libraries\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;
use Survey;

class OpHandlerSurveyUpdate implements OpHandlerInterface
{
    /**@var Survey */
    protected $survey = null;

    public function __construct(OpInterface $op)
    {
        $this->survey = Survey::model()->findByPk($op->getEntityId());
    }

    public function canHandle(OpInterface $op): bool
    {
        // the operation should be update. where do i get this info from?
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;

        // the entity should be survey
        $isSurveyEntity = $op->getEntityType() ==='survey';

        return $isUpdateOperation && $isSurveyEntity && $this->survey!== null;
    }
    /**
     * Saves the changes to the database.
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        //here we should get the props from the op
        $props =$op->getProps();
        if ($props === null) {
            throw new OpHandlerException(
                printf(
                    'No values to update for entity %s',
                    $op->getEntityType()
                )
            );
        }
        $this->survey->setAttributes($props);
        if (!$this->survey->save()) {
            throw new OpHandlerException(
                printf(
                    'Could not update survey (id: %s)',
                    $op->getEntityId()
                )
            );
        }
    }
}
