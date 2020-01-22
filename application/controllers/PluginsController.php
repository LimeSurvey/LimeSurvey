<?php

/**
 * @todo Not used, copied to admin/pluginmanager.php. Delete this file?
 * @todo Actually, it's used for action direct.
 */
class PluginsController extends LSYii_Controller
{

    /**
     * Launch the event newDirectRequest
     * @param $plugin : the target
     * @param $function : the function to call from the plugin
     */
    public function actionDirect($plugin, $function = null)
    {
        $oEvent = new PluginEvent('newDirectRequest');
        // The intended target of the call.
        $oEvent->set('target', $plugin);
        // The name of the function.
        $oEvent->set('function', $function);
        $oEvent->set('request', App()->request);

        App()->getPluginManager()->dispatchEvent($oEvent);
        $sOutput = '';
        foreach ($oEvent->getAllContent() as $content) {
            $sOutput .= $content->getContent();
        }
        if (!empty($sOutput)) {
            $this->renderText($sOutput);
        }
    }

    /**
     * Launch the event newUnsecureRequest
     * @param $plugin : the target
     * @param $function : the function to call from the plugin
     */
    public function actionUnsecure($plugin, $function = null)
    {
        $oEvent = new PluginEvent('newUnsecureRequest');
        // The intended target of the call.
        $oEvent->set('target', $plugin);
        // The name of the function.
        $oEvent->set('function', $function);
        $oEvent->set('request', App()->request);

        App()->getPluginManager()->dispatchEvent($oEvent);
        $sOutput = '';
        foreach ($oEvent->getAllContent() as $content) {
            $sOutput .= $content->getContent();
        }
        if (!empty($sOutput)) {
            $this->renderText($sOutput);
        }
    }

    /**
     * Show list of plugins
     * @deprecated
     * @return void
     */
    public function actionIndex()
    {
        // Or shortcut for actionDirect ?
        $this->redirect($this->createUrl("admin/pluginmanager/sa/index"));
    }

}
