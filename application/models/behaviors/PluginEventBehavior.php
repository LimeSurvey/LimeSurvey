<?php

use LimeSurvey\PluginManager\PluginEvent;

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

    public function afterDelete(CEvent $event)
    {
        $this->dispatchPluginModelEvent('after' . get_class($this->owner) . 'Delete', null, [], $event);
        $this->dispatchDynamic('after', 'Delete', $event);
        $this->dispatchPluginModelEvent('afterModelDelete', null, [], $event);
    }

    public function afterSave(CEvent $event)
    {
        $pluginManager = App()->getPluginManager();
        // Don't propagate event if we're in a shutdown, since it will lead to an infinite loop.
        if ($pluginManager->shutdownObject->isEnabled()) {
            return;
        }
        $this->dispatchPluginModelEvent('after' . get_class($this->owner) . 'Save', null, [], $event);
        $this->dispatchDynamic('after', 'Save', $event);
        $this->dispatchPluginModelEvent('afterModelSave', null, [], $event);
    }


    public function beforeDelete(CModelEvent $event)
    {
        $this->dispatchPluginModelEvent('before' . get_class($this->owner) . 'Delete', null, [], $event);
        $this->dispatchDynamic('before', 'Delete', $event);
        $this->dispatchPluginModelEvent('beforeModelDelete', null, [], $event);
    }


    public function beforeSave(CModelEvent $event)
    {
        $pluginManager = App()->getPluginManager();
        // Don't propagate event if we're in a shutdown, since it will lead to an infinite loop.
        if ($pluginManager->shutdownObject->isEnabled()) {
            return;
        }
        $this->dispatchPluginModelEvent('before' . get_class($this->owner) . 'Save', null, [], $event);
        $this->dispatchDynamic('before', 'Save', $event);
        $this->dispatchPluginModelEvent('beforeModelSave', null, [], $event);
    }

    /**
     * Log parent event for dynamic (currently Token and Response)
     * and related id
     * @param string $when
     * @param string $what
     * @param CModelEvent $event
     * @return PluginEvent the dispatched event
     */
    private function dispatchDynamic($when, $what, $event = null)
    {
        if (is_subclass_of($this->owner, 'Dynamic')) {
            $params = array(
                'dynamicId' => $this->owner->getDynamicId()
            );
            return $this->dispatchPluginModelEvent($when . get_parent_class($this->owner) . $what, null, $params, $event);
        }
    }
    /**
     * method for dispatching plugin events
     *
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param string $sEventName event name to dispatch
     * @param array $criteria array containing attributes, conditions and params for the filter query
     * @param array $eventParams array of params for event
     * @param CModelEvent $event the modelEvent
     * @return PluginEvent the dispatched event
     */
    public function dispatchPluginModelEvent($sEventName, $criteria = null, $eventParams = array(), $event = null)
    {
        $oPluginEvent = new PluginEvent($sEventName, $this);
        $oPluginEvent->set('model', $this->owner);
        $oPluginEvent->set('modelEvent', $event);
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
