<?php

class PluginsController extends LSYii_Controller
{

    public $layout = 'main';

    /**
     * Stored dynamic properties set and unset via __get and __set.
     * @var array of mixed.
     */
    protected $properties = array();

    public function __get($property)
    {
        return $this->properties[$property];
    }

    public function __set($property, $value)
    {
        $this->properties[$property] = $value;
    }

    public function _init()
    {
        parent::_init();
        Yii::app()->bootstrap->init();      // Make sure bootstrap css is rendered in time
    }

    public function accessRules()
    {
        $aRules = array(
            array('allow', 'roles' => array('administrator')),
            array('allow', 'actions' => array('direct')),
            array('deny')
        );


        // Note the order; rules are numerically indexed and we want to
        // parents rules to be executed only if ours dont apply.
        return array_merge($aRules, parent::accessRules());
    }

    public function actionActivate($id)
    {
        $oPlugin = Plugin::model()->findByPk($id);
        if (!is_null($oPlugin))
        {
            $iStatus = $oPlugin->active;
            if ($iStatus == 0)
            {
                // Load the plugin:
                App()->getPluginManager()->loadPlugin($oPlugin->name, $id);
                $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeActivate', $this), $oPlugin->name);
                if ($result->get('success', true))
                {
                    $iStatus = 1;
                } else
                {
                    $sMessage = $result->get('message', gT('Failed to activate the plugin.'));
                    App()->user->setFlash('pluginActivation', $sMessage);
                    $this->redirect(array('plugins/'));
                }
            }
            $oPlugin->active = $iStatus;
            $oPlugin->save();
        }
        $this->redirect(array('plugins/'));
    }

    public function actionConfigure($id)
    {
        $arPlugin      = Plugin::model()->findByPk($id)->attributes;
        $oPluginObject = App()->getPluginManager()->loadPlugin($arPlugin['name'], $arPlugin['id']);

        if ($arPlugin === null)
        {
            Yii::app()->user->setFlash('pluginmanager', 'Plugin not found');
            $this->redirect(array('plugins/'));
        }
        
        // If post handle data, yt0 seems to be the submit button
        if (App()->request->isPostRequest && App()->request->getPost('yt0'))
        {

            $aSettings = $oPluginObject->getPluginSettings(false);
            $aSave     = array();
            foreach ($aSettings as $name => $setting)
            {
                $aSave[$name] = App()->request->getPost($name, null);
            }
            $oPluginObject->saveSettings($aSave);

            Yii::app()->user->setFlash('pluginmanager', 'Settings saved');
            $this->forward('plugins/index', true);
        }

        // Prepare settings to be send to the view.
        $aSettings = $oPluginObject->getPluginSettings();
        if (empty($aSettings))
        {
            // And show a message
            Yii::app()->user->setFlash('pluginmanager', 'This plugin has no settings');
            $this->forward('plugins/index', true);
        }

        // Send to view plugin porperties: name and description
        $aPluginProp = App()->getPluginManager()->getPluginInfo($arPlugin['name']);

        $this->render('/plugins/configure', array('settings' => $aSettings, 'plugin' => $arPlugin, 'properties' => $aPluginProp));
    }

    public function actionDeactivate($id)
    {
        $oPlugin = Plugin::model()->findByPk($id);
        if (!is_null($oPlugin))
        {
            $iStatus = $oPlugin->active;
            if ($iStatus == 1)
            {
                $result = App()->getPluginManager()->dispatchEvent(new PluginEvent('beforeDeactivate', $this), $oPlugin->name);
                if ($result->get('success', true))
                {
                    $iStatus = 0;
                } else
                {
                    $message = $result->get('message', gT('Failed to deactivate the plugin.'));
                    App()->user->setFlash('pluginActivation', $message);
                    $this->redirect(array('plugins/'));
                }
            }
            $oPlugin->active = $iStatus;
            $oPlugin->save();
        }
        $this->redirect(array('plugins/'));
    }

    public function actionDirect($plugin, $function)
    {
        $oEvent = new PluginEvent('newDirectRequest');
        // The intended target of the call.
        $oEvent->set('target', $plugin);
        // The name of the function.
        $oEvent->set('function', $function);
        $oEvent->set('request', App()->request);

        App()->getPluginManager()->dispatchEvent($oEvent);

        $sOutput = '';
        foreach ($oEvent->getAllContent() as $content)
        {
            $sOutput .= $content->getContent();
        }

        if (!empty($sOutput))
        {
            $this->renderText($sOutput);
        }
    }

    public function actionIndex()
    {
        $oPluginManager = App()->getPluginManager();

        // Scan the plugins folder.
        $aDiscoveredPlugins = $oPluginManager->scanPlugins();
        $aInstalledPlugins  = $oPluginManager->getInstalledPlugins();
        $aInstalledNames    = array_map(function ($installedPlugin) {
                return $installedPlugin->name;
            }, $aInstalledPlugins);

        // Install newly discovered plugins.
        foreach ($aDiscoveredPlugins as $discoveredPlugin)
        {
            if (!in_array($discoveredPlugin['pluginClass'], $aInstalledNames))
            {
                $oPlugin         = new Plugin();
                $oPlugin->name   = $discoveredPlugin['pluginClass'];
                $oPlugin->active = 0;
                $oPlugin->save();
            }
        }

        $aoPlugins = Plugin::model()->findAll();
        $data      = array();
        foreach ($aoPlugins as $oPlugin)
        {
            /* @var $plugin Plugin */
            if (array_key_exists($oPlugin->name, $aDiscoveredPlugins))
            {
                $aPluginSettings = App()->getPluginManager()->loadPlugin($oPlugin->name, $oPlugin->id)->getPluginSettings(false);
                $data[]          = array(
                    'id'          => $oPlugin->id,
                    'name'        => $aDiscoveredPlugins[$oPlugin->name]['pluginName'],
                    'description' => $aDiscoveredPlugins[$oPlugin->name]['description'],
                    'active'      => $oPlugin->active,
                    'settings'    => $aPluginSettings,
                    'new'         => !in_array($oPlugin->name, $aInstalledNames)
                );
            } else
            {
                // This plugin is missing, maybe the files were deleted but the record was not removed from the database
                // Now delete this record. Depending on the plugin the settings will be preserved
                App()->user->setFlash('pluginDelete' . $oPlugin->id, sprintf(gT("Plugin '%s' was missing and is removed from the database."), $oPlugin->name));
                $oPlugin->delete();
            }
        }
        echo $this->render('/plugins/index', compact('data'));
    }

    public function filters()
    {
        $aFilters = array(
            'accessControl'
        );
        return array_merge(parent::filters(), $aFilters);
    }

}

?>
