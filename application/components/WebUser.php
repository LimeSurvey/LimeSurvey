<?php
    /**
     * @property-read ls\pluginmanager\iUser $model;
     */
    class WebUser extends CWebUser
    {
        /**
         * Returns the plugin responsible for authenticating the current user.
         * @return ls\pluginmanager\iAuthenticationPlugin
         */
        public function getPlugin() {
            return App()->pluginManager->getPlugin($this->getState('authenticationPlugin'));
        }

        /**
         * This function is only called if 
         */
        public function getModel() {
            $this->setState('model', $this->getPlugin()->getUser($this->getId()));
            return $this->getState('model');
        }


    }
?>