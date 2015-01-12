<?php
namespace ls\pluginmanager;
abstract class PluginController extends \CController {
    public function filters() {
        return [
            'accessControl'
        ];
    }
    
    public function accessRules() {
            return array_merge([
                ['allow', 'roles' => 'superadmin'],
                // By default deny access to plugin controllers.
                ['deny']
            ], parent::accessRules());
        }
    
}