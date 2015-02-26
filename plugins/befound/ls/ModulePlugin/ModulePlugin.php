<?php
    namespace befound\ls\ModulePlugin;
    /**
     * Example plugin that can not be activated.
     */
    class ModulePlugin extends \ls\pluginmanager\PluginModule
    {
        public $defaultController = 'dashboard';
        public $controllerNamespace = "befound\ls\ModulePlugin\\controllers";
        
        protected $replaceComponents = [
            'user' => [
                'class' => 'CWebUser',
                'stateKeyPrefix' => __CLASS__,
                'loginUrl' => ['moduleplugin/dashboard/login']
            ],
            'authManager' => [
                'class' => 'CPhpAuthManager'
            ]
        ];
        
        public $originalComponents = [
            
        ];
        
        /**
         * This function is only called if the active controller belongs to this module.
         */
        public function initIfActive() {
            foreach($this->replaceComponents as $id => $config) {
                $this->originalComponents[$id] = App()->getComponent($id, false);
            }
            \Yii::app()->setComponents($this->replaceComponents);
        }
    }
?>