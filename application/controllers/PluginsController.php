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
        $this->render('configure', ['plugin' => $plugin]);
    }

    public function actionDeactivate($id)
    {
        if ($id === App()->authManager->authorizationPlugin->id) {
            App()->user->setFlash('error', "Cannot disable currently active authorization plugin.");
        } elseif (in_array($id, SettingGlobal::get('authenticationPlugins', []))) {
            App()->user->setFlash('error', "Cannot disable currently active authentication plugin.");
        }
        
        
        foreach (App()->pluginManager->scanPlugins() as $pluginConfig) {
            if ($pluginConfig->id === $id) {
                $pluginConfig->active = false;
                $pluginConfig->save();
            }
        }
        $this->redirect(['plugins/']);
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

    public function actionConfigureAuth() {
        $request = App()->request;
        if (App()->request->isPostRequest && null !== $id = $request->getParam('authorizationPlugin')) {
            $plugin = App()->pluginManager->getPlugin($id);
            if ($plugin instanceof IAuthManager && SettingGlobal::set('authorizationPlugin', $request->getParam('authorizationPlugin'))) {
                App()->user->setFlash('success', gT('Authorization configuration updated.'));
            }
            $authenticationPlugins = $request->getParam('authenticationPlugins');
            if (is_array($authenticationPlugins) && array_intersect($authenticationPlugins, array_keys(App()->pluginManager->getAuthenticators())) == $authenticationPlugins) {
                SettingGlobal::set('authenticationPlugins', $authenticationPlugins);
                App()->user->setFlash('success', gT('Authorization and authentication configuration updated.'));
            }
        }
        $this->redirect(['plugins/index']);
    }
    public function actionIndex()
    {
        $pm = App()->pluginManager;
        $plugins = new CArrayDataProvider($pm->scanPlugins());
        $loadedPlugins = $pm->loadPlugins();
        return $this->render('index', [
            'plugins' => $plugins, 
            'loadedPlugins' => $loadedPlugins, 
            'authorizers' => $pm->getAuthorizers(), 
            'authenticators' => $pm->getAuthenticators()
        ]);
    }

    public function filters()
    {
        return array_merge(parent::filters(), ['accessControl']);
    }

}

?>
