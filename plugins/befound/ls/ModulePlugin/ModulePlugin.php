<?php
    namespace befound\ls\ModulePlugin;
    /**
     * Example plugin that can not be activated.
     */
    class ModulePlugin extends \ls\pluginmanager\PluginModule
    {
        public $defaultController = 'dashboard';
        public $controllerNamespace;
        
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
        
        public function init() {
            parent::init();
            $this->controllerNamespace = __NAMESPACE__ . '\\controllers';
        }

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