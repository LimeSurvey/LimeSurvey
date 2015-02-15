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
        /**
         * Since this module does not get any config passed in, apply it here manually.
         * @param type $config
         */
        public function configure($config) {
            parent::configure($config);

            $this->controllerNamespace = __NAMESPACE__ . '\\controllers';
            foreach($this->replaceComponents as $id => $config) {
                $this->originalComponents[$id] = App()->getComponent($id, false);
            }
            \Yii::app()->setComponents($this->replaceComponents);
        }
        
        public function __construct($id, $parent, $config = null) {
            parent::__construct($id, $parent, $config);
//            throw new \Exception('mp created');
        }
    }
?>