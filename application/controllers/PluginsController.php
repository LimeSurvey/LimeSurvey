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

        public function accessRules()
        {
            $rules = array(
                array('allow', 'roles' => array('administrator')),
				array('allow', 'actions' => array('direct')),
                array('deny')
            );


            // Note the order; rules are numerically indexed and we want to
            // parents rules to be executed only if ours dont apply.
            return array_merge($rules, parent::accessRules());
        }
        public function actionIndex()
        {
            $pluginManager = App()->getPluginManager();
            
            // Scan the plugins folder.
            $discoveredPlugins = $pluginManager->scanPlugins();
            $installedPlugins = $pluginManager->getInstalledPlugins();
            $installedNames = array_map(function ($installedPlugin) { return $installedPlugin->name; }, $installedPlugins);

            // Install newly discovered plugins.
            foreach ($discoveredPlugins as $discoveredPlugin)
            {
                if (!in_array($discoveredPlugin['pluginClass'], $installedNames))
                {
                    $plugin = new Plugin();
                    $plugin->name = $discoveredPlugin['pluginClass'];
                    $plugin->active = 0;
                    $plugin->save();
                }
            }
            
            $plugins = Plugin::model()->findAll();
            $data = array();
            foreach ($plugins as $plugin)
            {
                /* @var $plugin Plugin */
                if (array_key_exists($plugin->name, $discoveredPlugins)) {
                    $pluginSettings = App()->getPluginManager()->loadPlugin($plugin->name, $plugin->id)->getPluginSettings(false);
                    $data[] = array(
                        'id' => $plugin->id,
                        'name' => $discoveredPlugins[$plugin->name]['pluginName'],
                        'description' => $discoveredPlugins[$plugin->name]['description'],
                        'active' => $plugin->active,
                        'settings'=>$pluginSettings,
                        'new' => !in_array($plugin->name, $installedNames)
                    );
                } else {
                    // This plugin is missing, maybe the files were deleted but the record was not removed from the database
                    // Now delete this record. Depending on the plugin the settings will be preserved
                    App()->user->setFlash('pluginDelete'.$plugin->id,sprintf(gT("Plugin '%s' was missing and is removed from the database."), $plugin->name));
                    $plugin->delete();
                }
            }
            echo $this->render('/plugins/index', compact('data'));
        }
        
         public function actionActivate($id)
        {
            $plugin = Plugin::model()->findByPk($id);
            if (!is_null($plugin)) {
                $status = $plugin->active;
                if ($status == 0) {
                    // Load the plugin:
                    App()->getPluginManager()->loadPlugin($plugin->name, $id);
                    $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeActivate', $this), $plugin->name);
                    if ($result->get('success', true)) {
                        $status = 1;
                    } else {
                        $message = $result->get('message', gT('Failed to activate the plugin.'));
                        App()->user->setFlash('pluginActivation', $message);
                        $this->redirect(array('plugins/'));
                    }
                }
                $plugin->active = $status;
                $plugin->save();
            }
            $this->redirect(array('plugins/'));
        }
        
        public function actionDeactivate($id)
        {
            $plugin = Plugin::model()->findByPk($id);
            if (!is_null($plugin)) {
                $status = $plugin->active;
                if ($status == 1) {
                    $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeDeactivate', $this), $plugin->name);
                    if ($result->get('success', true)) {
                        $status = 0;
                    } else {
                        $message = $result->get('message', gT('Failed to deactivate the plugin.'));
                        App()->user->setFlash('pluginActivation', $message);
                        $this->redirect(array('plugins/'));
                    }
                }                
                $plugin->active = $status;
                $plugin->save();
            }
            $this->redirect(array('plugins/'));
        }

		public function actionDirect($plugin, $function)
		{
			$event = new PluginEvent('newDirectRequest');
			// The intended target of the call.
			$event->set('target', $plugin);
			// The name of the function.
			$event->set('function', $function);
			$event->set('request', App()->request);

			App()->getPluginManager()->dispatchEvent($event);
			
			$out = '';
			foreach($event->getAllContent() as $content)
			{
				$out .= $content->getContent();
			}

			if (!empty($out))
			{
				$this->renderText($out);
			}
		}
         public function actionConfigure($id)
         {
             $plugin = Plugin::model()->findByPk($id)->attributes;
             $pluginObject = App()->getPluginManager()->loadPlugin($plugin['name'], $plugin['id']);
             
             if ($plugin === null)
             {
                 Yii::app()->user->setFlash('pluginmanager', 'Plugin not found');
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
				Yii::app()->user->setFlash('pluginmanager', 'Settings saved');
             }
             
             $settings =  $pluginObject->getPluginSettings();
             if (empty($settings)) {
                 // And show a message
                 Yii::app()->user->setFlash('pluginmanager', 'This plugin has no settings');
                 $this->forward('plugins/index', true);
             }
             $this->render('/plugins/configure', compact('settings', 'plugin'));
             
         }
         
         public function filters()
         {
             $filters = array(
                 'accessControl'
             );
             return array_merge(parent::filters(), $filters);
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
