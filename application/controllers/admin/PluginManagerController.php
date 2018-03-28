<?php

/**
 */
class PluginManagerController extends Survey_Common_Action
{
    public function init()
    {
    }

    /**
     * Overview for plugins
     * Copied from PluginsController 2015-10-02
     */
    public function index()
    {
        $oPluginManager = App()->getPluginManager();

        $oPluginManager->scanPlugins();

        $jsFile = App()->getConfig('adminscripts') . 'plugin_manager.js';
        App()->getClientScript()->registerScriptFile($jsFile);

        // Scan the plugins folder.
        /*
        $aDiscoveredPlugins = $oPluginManager->scanPlugins();
        $aInstalledPlugins  = $oPluginManager->getInstalledPlugins();
        $aInstalledNames    = array_map(
            function ($installedPlugin) {
                return $installedPlugin->name;
            },
            $aInstalledPlugins
        );

        // Install newly discovered plugins.
        foreach ($aDiscoveredPlugins as $discoveredPlugin) {
            if (!in_array($discoveredPlugin['pluginClass'], $aInstalledNames)) {
                $oPlugin         = new Plugin();
                $oPlugin->name   = $discoveredPlugin['pluginClass'];
                $oPlugin->active = 0;
                $oPlugin->save();
                //New plugin registration
                $event = new PluginEvent('onPluginRegistration');
                $event->set('pluginName', $oPlugin->name);
                App()->getPluginManager()->dispatchEvent($event);
            }
        }
         */

        $aoPlugins = Plugin::model()->findAll(array('order' => 'name'));
        $data      = array();
        foreach ($aoPlugins as $oPlugin) {
            $data[] = [
                'id'          => $oPlugin->id,
                'name'        => $oPlugin->name,
                'load_error'  => $oPlugin->load_error,
                'description' => '',
                'active'      => $oPlugin->active,
                'settings'    => []
            ];
        }

        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSize', intval(Yii::app()->request->getParam('pageSize')));
        }

        $aData['fullpagebar']['returnbutton']['url'] = 'index';
        $aData['fullpagebar']['returnbutton']['text'] = gT('Return to admin home');
        $aData['data'] = $data;
        $aData['scanFilesUrl'] = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'scanFiles',
            ]
        );

        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect(array('/admin'));
        }
        $this->_renderWrappedTemplate('pluginmanager', 'index', $aData);
    }

    /**
     * Activate or deactivate a plugin
     *
     * @return void
     */
    public function changestate()
    {
        //Yii::app()->request->validateCsrfToken();
        $id = Yii::app()->request->getPost('id');
        $type = Yii::app()->request->getPost('type');
        if ($type == "activate") {
            $this->activate($id);
        } else if ($type == "deactivate") {
            $this->deactivate($id);
        }
    }

    /**
     * Scan files in plugin folder and add them to the database.
     * @return void
     */
    public function scanFiles()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect(['/admin/pluginmanager']);
        }

        $oPluginManager = App()->getPluginManager();
        $result = $oPluginManager->scanPlugins();

        Yii::app()->setFlashMessage(
            sprintf(
                gT('Found %s plugins in file system'),
                count($result)
            ),
            'notice'
        );

        $data = [];
        $data['result'] = $result;
        $data['fullpagebar']['returnbutton']['url'] = 'pluginmanager';
        $data['fullpagebar']['returnbutton']['text'] = gT('Return to plugin manager');

        $this->_renderWrappedTemplate(
            'pluginmanager',
            'scanFilesResult',
            $data
        );

        //$indexUrl = $this->getController()->createUrl('/admin/pluginmanager');
        //$this->getController()->redirect($indexUrl);
    }

    /**
     * Activate a plugin
     *
     * @todo Defensive programming
     * @param int $id Plugin id
     * @return void
     */
    private function activate($id)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect(array('/admin/pluginmanager/sa/index'));
        }
        $oPlugin = Plugin::model()->findByPk($id);
        if (!is_null($oPlugin)) {
            $iStatus = $oPlugin->active;
            if ($iStatus == 0) {
                // Load the plugin:
                App()->getPluginManager()->loadPlugin($oPlugin->name, $id);
                $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeActivate', $this), $oPlugin->name);
                if ($result->get('success', true)) {
                    $iStatus = 1;
                } else {
                    $customMessage = $result->get('message');
                    if ($customMessage) {
                        Yii::app()->user->setFlash('error', $customMessage);
                    } else {
                        Yii::app()->user->setFlash('error', gT('Failed to activate the plugin.'));
                    }

                    $this->getController()->redirect(array('admin/pluginmanager/sa/index/'));
                }
            }
            $oPlugin->active = $iStatus;
            $oPlugin->save();
            Yii::app()->user->setFlash('success', gT('Plugin was activated.'));
        }
        $this->getController()->redirect(array('admin/pluginmanager/sa/index/'));
    }

    /**
     * Deactivate plugin with $id
     *
     * @param int $id
     * @return void
     */
    private function deactivate($id)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect(array('/admin/pluginmanager/sa/index'));
        }
        $oPlugin = Plugin::model()->findByPk($id);
        if (!is_null($oPlugin)) {
            $iStatus = $oPlugin->active;
            if ($iStatus == 1) {
                $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeDeactivate', $this), $oPlugin->name);
                if ($result->get('success', true)) {
                    $iStatus = 0;
                } else {
                    $customMessage = $result->get('message');
                    if ($customMessage) {
                        Yii::app()->user->setFlash('error', $customMessage);
                    } else {
                        Yii::app()->user->setFlash('error', gT('Failed to activate the plugin.'));
                    }

                    $this->getController()->redirect(array('admin/pluginmanager/sa/index/'));
                }
            }
            $oPlugin->active = $iStatus;
            $oPlugin->save();
            Yii::app()->user->setFlash('success', gT('Plugin was deactivated.'));
        }
        $this->getController()->redirect(array('admin/pluginmanager/sa/index/'));
    }

    /**
     * Configure for plugin
     */
    public function configure($id)
    {
        $url = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'index'
            ]
        );
        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->setFlashMessage(gT("No permission"), 'error');
            $this->getController()->redirect($url);
        }

        $plugin      = Plugin::model()->findByPk($id);
        $oPluginObject = App()->getPluginManager()->loadPlugin($plugin->name, $plugin->id);

        if (!$oPluginObject->readConfigFile()) {
            Yii::app()->user->setFlash('error', gT('Found no configuration file for this plugin.'));
            $this->getController()->redirect($url);
        }

        if ($plugin === null) {
            Yii::app()->user->setFlash('error', gT('The plugin was not found.'));
            $this->getController()->redirect($url);
        }

        // If post handle data, yt0 seems to be the submit button
        // TODO: Break out to separate method.
        if (App()->request->isPostRequest) {
            if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
                Yii::app()->setFlashMessage(gT("No permission"), 'error');
                $this->getController()->redirect($url);
            }
            $aSettings = $oPluginObject->getPluginSettings(false);
            $aSave     = array();
            foreach ($aSettings as $name => $setting) {
                $aSave[$name] = App()->request->getPost($name, null);
            }
            $oPluginObject->saveSettings($aSave);
            Yii::app()->user->setFlash('success', gT('The plugin settings were saved.'));
            if (App()->request->getPost('redirect')) {
                $this->getController()->redirect(App()->request->getPost('redirect'), true);
            }
        }

        // Prepare settings to be send to the view.
        $aSettings = $oPluginObject->getPluginSettings();

        // Send to view plugin porperties: name and description
        $aPluginProp = App()->getPluginManager()->getPluginInfo($plugin->name);

        $fullPageBar = [];
        $fullPageBar['returnbutton']['url'] = 'admin/pluginmanager/sa/index';
        $fullPageBar['returnbutton']['text'] = gT('Return to plugin list');

        $this->_renderWrappedTemplate(
            'pluginmanager',
            'configure',
            [
                'settings'     => $aSettings,
                'plugin'       => $plugin,
                'pluginObject' => $oPluginObject,
                'properties'   => $aPluginProp,
                'fullpagebar'  => $fullPageBar
            ]
        );
    }

    /**
     * Set load_error to 0 for plugin with id $pluginId.
     * This makes it possible to try to load the plugin again,
     * if a fix for previous load error has been implemented.
     *
     * @param int $pluginId
     * @return void
     */
    public function resetLoadError($pluginId)
    {
        $url = $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'index'
            ]
        );

        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect($url);
        }

        $pluginId = (int) $pluginId;
        $plugin = Plugin::model()->find('id = :id', [':id' => $pluginId]);
        if ($plugin) {
            $plugin->load_error = 0;
            $plugin->load_error_message = '';
            $result = $plugin->update();
            if ($result) {
                Yii::app()->user->setFlash('success', sprintf(gt('Reset load error for plugin %d'), $pluginId));
            } else {
                Yii::app()->user->setFlash('error', sprintf(gt('Could not update plugin %d'), $pluginId));
            }
            $this->getController()->redirect($url);
        } else {
            Yii::app()->user->setFlash('error', sprintf(gt('Found no plugin with id %d'), $pluginId));
            $this->getController()->redirect($url);
        }
    }

    /**
     * Run when user click button to uninstall plugin.
     * @return void
     */
    public function uninstallPlugin()
    {
        // Check permissions.
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->setFlashMessage(gT('No permission'), 'error');
            $this->getController()->redirect($this->getPluginManagerUrl());
        }

        // Get plugin id from post.
        $request = Yii::app()->request;
        $pluginId = (int) $request->getPost('pluginId');

        $plugin = Plugin::model()->find('id = :id', [':id' => $pluginId]);

        // Check if plugin exists.
        if (empty($plugin)) {
            Yii::app()->setFlashMessage(
                sprintf(
                    gT('Found no plugin with id %d.'),
                    $pluginId
                ),
                'error'
            );
            $this->getController()->redirect($this->getPluginManagerUrl());
        } else {
            if ($plugin->delete()) {
                Yii::app()->setFlashMessage(gT('Plugin uninstalled.'), 'success');
            } else {
                Yii::app()->setFlashMessage(gT('Could not uninstall plugin.'), 'error');
            }
            $this->getController()->redirect($this->getPluginManagerUrl());
        }
    }

    /**
     * Return URL to plugin manager index..
     * @return string
     */
    protected function getPluginManagerUrl()
    {
        return $this->getController()->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'index'
            ]
        );
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'pluginmanager', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
