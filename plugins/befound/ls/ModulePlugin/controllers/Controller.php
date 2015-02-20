<?php
    namespace befound\ls\ModulePlugin\controllers;
    use \ls\pluginmanager\PluginController;
    
    /**
     * @property-read \befound\ls\ModulePlugin\ModulePlugin $module
     */
    class Controller extends PluginController {
        public $layout = 'main';
        
        public function accessRules() {
            return [['deny']];
        }
        
        public function init() {
            parent::init();
            $this->module->initIfActive();
        }
    }