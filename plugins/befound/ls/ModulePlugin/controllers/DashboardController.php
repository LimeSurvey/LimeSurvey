<?php
    namespace befound\ls\ModulePlugin\controllers;
    use \ls\pluginmanager\PluginController;
    
    class DashboardController extends PluginController{
        public function accessRules() {
            return array_merge([
                ['allow', 'roles' => ['superadmin']]
                
            ], parent::accessRules());
        }
        public function actionIndex() {
            echo 'nice';
        }
    }
