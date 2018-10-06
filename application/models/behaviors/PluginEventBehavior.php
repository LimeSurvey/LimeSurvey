<?php
    use \LimeSurvey\PluginManager\PluginEvent;
    class PluginEventBehavior extends CModelBehavior
    {
        public function events()
        {
            return array_merge(parent::events(), array(
                'onAfterDelete' => 'afterDelete',
                'onAfterSave' => 'afterSave',
                'onBeforeDelete' => 'beforeDelete',
                'onBeforeSave' => 'beforeSave',
            ));
        }

        public function afterDelete(CEvent $event)
        {
            $this->dispatchPluginModelEvent('after'.get_class($this->owner).'Delete');
            $this->_dispatchDynamic('after','Delete');
            $this->dispatchPluginModelEvent('afterModelDelete');
        }
        public function afterSave(CEvent $event)
        {
            $this->dispatchPluginModelEvent('after'.get_class($this->owner).'Save');
            $this->_dispatchDynamic('after','Save');
            $this->dispatchPluginModelEvent('afterModelSave');
        }
        public function beforeDelete(CModelEvent $event)
        {
            $this->dispatchPluginModelEvent('before'.get_class($this->owner).'Delete');
            $this->_dispatchDynamic('before','Delete');
            $this->dispatchPluginModelEvent('beforeModelDelete');
        }

        public function beforeSave(CModelEvent $event)
        {
            $this->dispatchPluginModelEvent('before'.get_class($this->owner).'Save');
            $this->_dispatchDynamic('before','Save');
            $this->dispatchPluginModelEvent('beforeModelSave');
        }

        /**
         * Log parent event for dynamic (currently Token and Response)
         * and related id
         * @param string $when
         * @param string $what
         * @return PluginEvent the dispatched event
         */
        private function _dispatchDynamic($when,$what) {
            if(is_subclass_of($this->owner,'Dynamic'))
            {
                $oPluginEvent = new PluginEvent($when.get_parent_class($this->owner).$what, $this);
                $oPluginEvent->set('model', $this->owner);
                if(in_array(get_parent_class($this->owner),array("Token","Response"))) { // We know dynamicId is survey
                    $oPluginEvent->set('iSurveyID', $this->owner->dynamicId);
                }
                $oPluginEvent->set('dynamicId', $this->owner->dynamicId);
                return App()->getPluginManager()->dispatchEvent($oPluginEvent);
            }
        }
        /**
         * method for dispatching plugin events
         *
         * See {@link find()} for detailed explanation about $condition and $params.
         * @param string $sEventName event name to dispatch
         * @param array	$criteria array containing attributes, conditions and params for the filter query
         * @return PluginEvent the dispatched event
         */
        public function dispatchPluginModelEvent($sEventName, $criteria = null)
        {
            $oPluginEvent = new PluginEvent($sEventName, $this);
            $oPluginEvent->set('model', $this->owner);
            Yii::log(CVarDumper::dumpAsString([get_class($this->owner)], 3, false), 'info','application.plugins.event');;
            if (isset($criteria)) {
                $oPluginEvent->set('filterCriteria', $criteria);
            }
            return App()->getPluginManager()->dispatchEvent($oPluginEvent);
        }
    }
