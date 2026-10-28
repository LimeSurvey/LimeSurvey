<?php

use LimeSurvey\PluginManager\PluginEvent;

/**
 * Dispatches plugin events for model save and delete operations.
 *
 * Every event dispatched from the save/delete hooks carries the underlying Yii
 * event in the 'modelEvent' parameter. In before* events this is a CModelEvent,
 * so a plugin can cancel the operation by setting
 * `$this->getEvent()->get('modelEvent')->isValid = false`. The model's save()
 * or delete() then returns false; plugins should add an error to the model
 * (e.g. `$model->addError(...)`) to tell the caller why. As with Yii's own
 * event handlers, the remaining plugin events are still dispatched after a
 * cancellation, so listeners can check `isValid` themselves.
 * In after* events 'modelEvent' is a plain CEvent.
 */
class PluginEventBehavior extends CModelBehavior
{
    public function events()
    {
        return array_merge(
            parent::events(),
            array(
                'onAfterDelete'  => 'afterDelete',
                'onAfterSave'    => 'afterSave',
                'onBeforeDelete' => 'beforeDelete',
                'onBeforeSave'   => 'beforeSave',
            )
        );
    }

    /**
     * Dispatch the after*Delete plugin events
     * @param CEvent $event
     * @return void
     */
    public function afterDelete(CEvent $event)
    {
        $eventParams = array('modelEvent' => $event);
        $this->dispatchPluginModelEvent('after' . get_class($this->owner) . 'Delete', null, $eventParams);
        $this->dispatchDynamic('after', 'Delete', $eventParams);
        $this->dispatchPluginModelEvent('afterModelDelete', null, $eventParams);
    }

    /**
     * Dispatch the after*Save plugin events
     * @param CEvent $event
     * @return void
     */
    public function afterSave(CEvent $event)
    {
        $pluginManager = App()->getPluginManager();
        // Don't propagate event if we're in a shutdown, since it will lead to an infinite loop.
        if ($pluginManager->shutdownObject->isEnabled()) {
            return;
        }
        $eventParams = array('modelEvent' => $event);
        $this->dispatchPluginModelEvent('after' . get_class($this->owner) . 'Save', null, $eventParams);
        $this->dispatchDynamic('after', 'Save', $eventParams);
        $this->dispatchPluginModelEvent('afterModelSave', null, $eventParams);
    }

    /**
     * Dispatch the before*Delete plugin events
     * A plugin can cancel the deletion by setting isValid of the 'modelEvent' parameter to false
     * @param CModelEvent $event
     * @return void
     */
    public function beforeDelete(CModelEvent $event)
    {
        $eventParams = array('modelEvent' => $event);
        $this->dispatchPluginModelEvent('before' . get_class($this->owner) . 'Delete', null, $eventParams);
        $this->dispatchDynamic('before', 'Delete', $eventParams);
        $this->dispatchPluginModelEvent('beforeModelDelete', null, $eventParams);
    }

    /**
     * Dispatch the before*Save plugin events
     * A plugin can cancel the save by setting isValid of the 'modelEvent' parameter to false
     * @param CModelEvent $event
     * @return void
     */
    public function beforeSave(CModelEvent $event)
    {
        $pluginManager = App()->getPluginManager();
        // Don't propagate event if we're in a shutdown, since it will lead to an infinite loop.
        if ($pluginManager->shutdownObject->isEnabled()) {
            return;
        }
        $eventParams = array('modelEvent' => $event);
        $this->dispatchPluginModelEvent('before' . get_class($this->owner) . 'Save', null, $eventParams);
        $this->dispatchDynamic('before', 'Save', $eventParams);
        $this->dispatchPluginModelEvent('beforeModelSave', null, $eventParams);
    }

    /**
     * Log parent event for dynamic (currently Token and Response)
     * and related id
     * @param string $when
     * @param string $what
     * @param array $eventParams additional params for the event
     * @return PluginEvent|null the dispatched event, null if the owner is not a Dynamic model
     */
    private function dispatchDynamic($when, $what, $eventParams = array())
    {
        if (is_subclass_of($this->owner, 'Dynamic')) {
            $eventParams['dynamicId'] = $this->owner->getDynamicId();
            return $this->dispatchPluginModelEvent($when . get_parent_class($this->owner) . $what, null, $eventParams);
        }
        return null;
    }
    /**
     * method for dispatching plugin events
     *
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param string $sEventName event name to dispatch
     * @param array $criteria array containing attributes, conditions and params for the filter query
     * @param array $eventParams array of params for event
     * @return PluginEvent the dispatched event
     */
    public function dispatchPluginModelEvent($sEventName, $criteria = null, $eventParams = array())
    {
        $oPluginEvent = new PluginEvent($sEventName, $this);
        $oPluginEvent->set('model', $this->owner);
        if (method_exists($this->owner, 'getSurveyId')) {
            $oPluginEvent->set('iSurveyID', $this->owner->getSurveyId());
            $oPluginEvent->set('surveyId', $this->owner->getSurveyId());
        }
        foreach ($eventParams as $param => $value) {
            $oPluginEvent->set($param, $value);
        }
        if (isset($criteria)) {
            $oPluginEvent->set('filterCriteria', $criteria);
        }
        return App()->getPluginManager()->dispatchEvent($oPluginEvent);
    }
}
