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
        $this->dispatchPluginModelEvent('after' . get_class($this->owner) . 'Delete');
        $this->dispatchDynamic('after', 'Delete');
        $this->dispatchPluginModelEvent('afterModelDelete');
    }

    public function afterSave(CEvent $event)
    {
        $pluginManager = App()->getPluginManager();
        // Don't propagate event if we're in a shutdown, since it will lead to an infinite loop.
        if ($pluginManager->shutdownObject->isEnabled()) {
            return;
        }
        $this->dispatchPluginModelEvent('after' . get_class($this->owner) . 'Save');
        $this->dispatchDynamic('after', 'Save');
        $this->dispatchPluginModelEvent('afterModelSave');
    }


    public function beforeDelete(CModelEvent $event)
    {
        $this->dispatchPluginModelEvent('before' . get_class($this->owner) . 'Delete');
        $this->dispatchDynamic('before', 'Delete');
        $this->dispatchPluginModelEvent('beforeModelDelete');
    }


    public function beforeSave(CModelEvent $event)
    {
        $pluginManager = App()->getPluginManager();
        // Don't propagate event if we're in a shutdown, since it will lead to an infinite loop.
        if ($pluginManager->shutdownObject->isEnabled()) {
            return;
        }
        $this->dispatchPluginModelEvent('before' . get_class($this->owner) . 'Save');
        $this->dispatchDynamic('before', 'Save');
        $this->dispatchPluginModelEvent('beforeModelSave');
    }

    /**
     * Log parent event for dynamic (currently Token and Response)
     * and related id
     * @param string $when
     * @param string $what
     * @return PluginEvent the dispatched event
     */
    private function dispatchDynamic($when, $what)
    {
        if (is_subclass_of($this->owner, 'Dynamic')) {
            $params = array(
                'dynamicId' => $this->owner->getDynamicId()
            );
            return $this->dispatchPluginModelEvent($when . get_parent_class($this->owner) . $what, null, $params);
        }
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
