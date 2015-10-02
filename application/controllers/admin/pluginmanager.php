<?php

class PluginManager extends Survey_Common_Action
{
    public function init()
    {
    }

    public function index()
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

        $this->_renderWrappedTemplate('pluginmanager', 'index', array('data' => $data));
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'pluginmanager', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
