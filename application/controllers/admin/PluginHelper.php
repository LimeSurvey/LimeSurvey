<?php

/**
 * @todo Better name?
 */
class PluginHelper extends SurveyCommonAction
{
    /** @inheritdoc */
    public $defaultAction = 'sidebody';

    /**
     * Helper function to let a plugin put content
     * into the side-body easily.
     *
     * @param int $surveyid
     * @param string $plugin Name of the plugin class
     * @param string $method Name of the plugin method
     * @return void
     */
    public function sidebody($surveyid = 0, $plugin = '', $method = '')
    {
        $aData = array();
        $aData['title_bar']['title'] = "";
        $aData['surveybar']['buttons']['view'] = false;
        $aData['activated'] = null;
        if ($surveyid) {
            $surveyid = sanitize_int($surveyid);
            $oSurvey = Survey::model()->findByPk($surveyid);
            if ($oSurvey) {
                $aData['surveybar']['buttons']['view'] = true;
                $aData['title_bar']['title'] = viewHelper::flatEllipsizeText($oSurvey->defaultlanguage->surveyls_title) . " (" . gT("ID") . ":" . $surveyid . ")";
                $aData['activated'] = $oSurvey->active;
            }
            // Plugin must test to send 404 if needed, maybe want to “create” a new survey, who know …
        }
        $aData['surveyid'] = $surveyid;
        $content = $this->getContent($surveyid, $plugin, $method);

        $aData['sidemenu'] = array();
        $aData['sidemenu']['state'] = false;
        $aData['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');
        $aData['content'] = $content;

        $this->renderWrappedTemplate(null, array('super/sidebody'), $aData);
    }

    /**
     * Helper function to let a plugin put content
     * into the full page wrapper easily.
     * @param string $plugin
     * @param string $method
     * @return void
     */
    public function fullpagewrapper($plugin, $method)
    {
        $aData = array();

        $content = $this->getContent(null, $plugin, $method);

        $aData['content'] = $content;
        $this->renderWrappedTemplate(null, 'super/dummy', $aData, 'layout_main.php');
    }

    /**
     * At ajax, just echo content
     * @param string $plugin
     * @param string $method
     */
    public function ajax($plugin, $method)
    {
        list($pluginInstance, $refMethod) = $this->getPluginInstanceAndMethod($plugin, $method);
        $request = Yii::app()->request;
        echo $refMethod->invoke($pluginInstance, $request);
    }

    /**
     *
     * @param string $pluginName
     * @param string $methodName
     * @return array
     */
    protected function getPluginInstanceAndMethod($pluginName, $methodName)
    {
        // Get plugin class, abort if not found
        try {
            $refClass = new ReflectionClass($pluginName);
        } catch (ReflectionException $ex) {
            throw new \CException("Can't find a plugin with class name $pluginName");
        }

        $record = Plugin::model()->findByAttributes(array('name' => $pluginName, 'active' => 1));
        if (empty($record)) {
            throw new Exception('Plugin with name ' . $pluginName . ' is not active or can\' be found');
        }

        $pluginManager = App()->getPluginManager();
        $pluginInstance = $refClass->newInstance($pluginManager, $record->id);
        /* Check if method is in allowed list */
        if (is_array($pluginInstance->allowedPublicMethods) && !in_array($methodName, $pluginInstance->allowedPublicMethods)) {
            throw new \CHttpException(400, sprintf(gT("Forbidden call of method %s for plugin %s"), $methodName, $pluginName));
        }

        Yii::app()->setPlugin($pluginInstance);
        // Get plugin method, abort if not found
        try {
            $refMethod = $refClass->getMethod($methodName);
        } catch (ReflectionException $ex) {
            throw new \CException('Plugin ' . $pluginName . ' has no method ' . $methodName);
        }

        return array($pluginInstance, $refMethod);
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
        list($pluginInstance, $refMethod) = $this->getPluginInstanceAndMethod($plugin, $method);
        return $refMethod->invoke($pluginInstance, $surveyId);
    }
}
