<?php
    class LSWebUser extends CWebUser
    {
        public function __construct()
        {
            // Try to fix missing language in plugin controller
            if (empty(Yii::app()->session['adminlang']))
            {
                 Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
            }

            Yii::app()->setLanguage(Yii::app()->session['adminlang']);
        }
        
        /**
         * Removes a state variable.
         * @param string $key
         */
        public function removeState($key)
        {
            $this->setState($key, null);
        }
        
        /**
         * Returns the plugin responsible for authenticating the current user.
         * @return \ls\pluginmanager\PluginBase
         */
        public function getPlugin() {
            return App()->pluginManager->getPlugin($this->getState('authenticationPlugin'));
        }

        public function getAttributes() {
            return $this->getState('attributes', []);
        }


    }
?>