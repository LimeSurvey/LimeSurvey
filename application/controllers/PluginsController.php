<?php
    /**
     * @property PluginSettingsHelper $PluginSettings
     */
    class PluginsController extends LSYii_Controller 
    {
        public $layout = 'main';
        /**
         * Stored dynamic properties set and unset via __get and __set.
         * @var array of mixed.
         */
        protected $properties = array();
        
        public function actionIndex()
        {
            // Scan the plugins folder.
            $discoveredPlugins = App()->getPluginManager()->scanPlugins();
            
            $installedPlugins = Plugin::model()->findAll();
            
            $installedNames = array_map(function ($installedPlugin) { return $installedPlugin->name; }, $installedPlugins);
            
            // Install newly discovered plugins.
            foreach ($discoveredPlugins as $discoveredPlugin)
            {
                if (!in_array($discoveredPlugin['name'], $installedNames))
                {
                    $plugin = new Plugin();
                    $plugin->name = $discoveredPlugin['name'];
                    $plugin->active = 0;
                    $plugin->save();
                }
            }
            
            $plugins = Plugin::model()->findAll();
            $data = array();
            foreach ($plugins as $plugin)
            {
                $data[] = array(
                    'id' => $plugin->id,
                    'name' => $plugin->name,
                    'active' => $plugin->active,
                    'new' => !in_array($plugin->name, $installedNames)
                );
            }
            echo $this->render('/plugins/index', compact('data'));
        }
        
         public function actionActivate($id)
        {
            $plugin = Plugin::model()->findByPk($id);
            if (!is_null($plugin)) {
                $status = $plugin->active;
                if ($status == 1) {
                    $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeDeactivate', $this), $plugin->name);
                    if ($result->get('success', true)) {
                        $status = 0;
                    } else {
                        echo "Failed to deactivate";
                        Yii::app()->end(); 
                    }

                } else {
                    $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeActivate', $this), $plugin->name);
                    if ($result->get('success', true)) {
                        $status = 1;
                    } else {
                        echo "Failed to activate";
                        Yii::app()->end();
                    }
                }
                $plugin->active = $status;
                $plugin->save();
            }
            $this->redirect(array('plugins/'));
        }

         public function actionConfigure($id)
         {
             $plugin = Plugin::model()->findByPk($id)->attributes;
             $pluginObject = App()->getPluginManager()->loadPlugin($plugin['name'], $plugin['id']);
             
             if ($plugin === null)
             {
                 /**
                  * @todo Add flash message "Plugin not found".
                  */
                 $this->redirect(array('plugins/'));
             }
             // If post handle data.
             if (App()->request->isPostRequest)
             {
                 $settings =  $pluginObject->getPluginSettings(false);
                 $save = array();
                 foreach ($settings as $name => $setting)
                 {
                     $save[$name] = App()->request->getPost($name, null);
                     
                 }
                 $pluginObject->saveSettings($save);
             }
                 
             
             $settings =  $pluginObject->getPluginSettings();
             $this->render('/plugins/configure', compact('settings', 'plugin'));
             
         }
         
         /**
          * Allows for array configuration that loads helpers.
          * @param type $view
          * @return boolean
          */
         
         public function beforeRender($view) {
             parent::beforeRender($view);
             return true;
         }
         
         public function __get($property)
         {
             return  $this->properties[$property];
         }
         
         public function __set($property, $value)
         {
             $this->properties[$property] = $value;
         }
         
          
    }
?>
