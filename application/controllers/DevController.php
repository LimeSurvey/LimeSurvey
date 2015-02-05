<?php

class DevController extends CController {
    
    public function actionIndex() {
        $result = "";
        $dir = __DIR__ . '/../../plugins';
        
        $result .= print_r(App()->pluginManager->scanPlugins(), true);
        
//        $plugins = \ls\pluginmanager\PluginConfig::findAll(true);
        
//        App()->pluginManager->subscribe("test", function($event) { var_dump($event); });
        
        $this->renderText('<pre>' . $result . '</pre>');
    }
}