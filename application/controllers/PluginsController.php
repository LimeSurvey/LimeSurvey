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
        $rules = [
            ['allow', 'roles' => ['superadmin']],
            ['allow', 'actions' => ['direct']],
            ['deny']
        ];


        // Note the order; rules are numerically indexed and we want to
        // parents rules to be executed only if ours dont apply.
        return array_merge($rules, parent::accessRules());
    }

    public function actionActivate($id)
    {
        foreach (App()->pluginManager->scanPlugins() as $pluginConfig) {
            if ($pluginConfig->id === $id) {
                $pluginConfig->active = true;
                $pluginConfig->save();
            }
        }
        $this->redirect(['plugins/']);
    }

    public function actionConfigure($id)
    {
        $pluginConfig = \ls\pluginmanager\PluginConfig::findAll(false)[$id];
        $plugin = App()->pluginManager->loadPlugin($pluginConfig);
        
        if (App()->request->isPostRequest) {
            $plugin->saveSettings(App()->request->getPost($plugin->id));
        }
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

    public function actionSetAuthorizer() {
        if (App()->request->isPostRequest && null !== $id = App()->request->getParam('authorizationPlugin')) {
            $plugin = App()->pluginManager->getPlugin($id);
            if ($plugin instanceof IAuthManager) {
                App()->setConfig('authorizationPlugin', App()->request->getParam('authorizationPlugin'));
            }
        }
        $this->redirect(['plugins/index']);
    }
    public function actionIndex()
    {
        
        $plugins = new CArrayDataProvider(App()->pluginManager->scanPlugins());
        $loadedPlugins = App()->pluginManager->loadPlugins();
        return $this->render('index', ['plugins' => $plugins, 'loadedPlugins' => $loadedPlugins]);
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
