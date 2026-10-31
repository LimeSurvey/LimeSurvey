<?php

class SurveyPermissionCriteriaPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing getSurveyPermissionCriteria event';
    protected static $name = 'SurveyPermissionCriteriaPlugin';

    /** @var int[] Surveys added to the criteria of users without global surveys read permission */
    public $grantedSurveyIds = [];

    /** @var array|null Values of the last dispatched event */
    public $lastEvent;

    public function init()
    {
        $this->subscribe('getSurveyPermissionCriteria');
    }

    /**
     * Adds the granted surveys to the criteria with OR
     * @return void
     */
    public function getSurveyPermissionCriteria()
    {
        $event = $this->getEvent();
        $this->lastEvent = [
            'userid' => $event->get('userid'),
            'hasGlobalRead' => $event->get('hasGlobalRead'),
        ];
        if ($event->get('hasGlobalRead') || empty($this->grantedSurveyIds)) {
            return;
        }
        $grantCriteria = new CDbCriteria();
        $grantCriteria->addInCondition('t.sid', $this->grantedSurveyIds);
        $event->get('criteria')->mergeWith($grantCriteria, 'OR');
    }
}
