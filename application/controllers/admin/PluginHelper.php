<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @todo Better name?
 */
class PluginHelper extends Survey_Common_Action
{
    /**
     * Helper function to let a plugin put content
     * into the side-body easily.
     * 
     * @param int $surveyId
     * @param string $plugin Name of the plugin class
     * @param string $method Name of the plugin method
     * @return void
     */
    public function sidebody($surveyId, $plugin, $method)
    {
        $aData = array();

        $surveyId = sanitize_int($surveyId);
        $surveyinfo = getSurveyInfo($surveyId);
        $aData['surveyid'] = $surveyId;

        $aData['surveybar']['buttons']['view']= true;
        $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$surveyId.")";

        $content = $this->getContent($surveyId, $plugin, $method);

        $aData['sidemenu'] = array();
        $aData['sidemenu']['state'] = false;
        $aData['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');
        $aData['content'] = $content;
        $aData['activated'] = $surveyinfo['active'];
        $aData['sideMenuOpen'] = false;  // TODO: Assume this for all plugins?
        $this->_renderWrappedTemplate(null, array('super/sidebody'), $aData);
        
    }

    /**
     * Get HTML content for side-body
     *
     * @param string $plugin Name of the plugin class
     * @param string $method Name of the plugin method
     * @return string
     */
    protected function getContent($surveyId, $plugin, $method)
    {
        // Get plugin class, abort if not found
        try
        {
            $refClass = new ReflectionClass($plugin);
        }
        catch (ReflectionException $ex)
        {
            throw new \CException("Can't find a plugin with class name $plugin");
        }

        $pluginManager = App()->getPluginManager();
        $pluginInstance = $refClass->newInstance($pluginManager, 0);

        // Get plugin method, abort if not found
        try
        {
            $refMethod = $refClass->getMethod($method);
        }
        catch (ReflectionException $ex)
        {
            throw new \CException("Plugin $plugin has no method $method");
        }

        return $refMethod->invoke($pluginInstance, $surveyId);

    }
}
