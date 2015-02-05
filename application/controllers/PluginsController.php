<?php

class PluginsController extends LSYii_Controller
{

    public $layout = 'main';

    /**
     * Stored dynamic properties set and unset via __get and __set.
     * @var array of mixed.
     */
//    protected $properties = array();
//
//    public function __get($property)
//    {
//        return $this->properties[$property];
//    }
//
//    public function __set($property, $value)
//    {
//        $this->properties[$property] = $value;
//    }

    public function _init()
    {
        parent::_init();
        Yii::app()->bootstrap->init();      // Make sure bootstrap css is rendered in time
    }

    public function accessRules()
    {
        $aRules = array(
            array('allow', 'roles' => array('superadmin')),
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
        $pluginConfig = \ls\pluginmanager\PluginConfig::findAll(false)[$id];
        $plugin = App()->pluginManager->loadPlugin($pluginConfig);
        
        if (App()->request->isPostRequest) {
            $plugin->saveSettings(App()->request->getPost($plugin->id));
        }
//        var_dump($settings);
//            var_dump($plugin);
//            die();
//        if ($arPlugin === null)
//        {
//            Yii::app()->user->setFlash('pluginmanager', 'Plugin not found');
//            $this->redirect(array('plugins/'));
//        }
//        
//        // If post handle data, yt0 seems to be the submit button
//        if (App()->request->isPostRequest)
//        {
//
//            $aSettings = $oPluginObject->getPluginSettings(false);
//            $aSave     = array();
//            foreach ($aSettings as $name => $setting)
//            {
//                $aSave[$name] = App()->request->getPost($name, null);
//            }
//            $oPluginObject->saveSettings($aSave);
//            App()->user->setFlash('pluginmanager', 'Settings saved');
//            if(!is_null(App()->request->getPost('redirect')))
//            {
//                $this->forward('plugins/index', true);
//            }
//        }
//
//        // Prepare settings to be send to the view.
//        $aSettings = $oPluginObject->getPluginSettings();
//        if (empty($aSettings))
//        {
//            // And show a message
//            Yii::app()->user->setFlash('pluginmanager', 'This plugin has no settings');
//            $this->forward('plugins/index', true);
//        }
//
//        // Send to view plugin porperties: name and description
//        $aPluginProp = App()->getPluginManager()->getPluginInfo($arPlugin['name']);

        $this->render('configure', ['plugin' => $plugin]);
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
        
        $plugins = new CArrayDataProvider(App()->pluginManager->scanPlugins());
        return $this->render('index', ['plugins' => $plugins]);
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
