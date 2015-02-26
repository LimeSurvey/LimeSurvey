<?php
namespace ls\controllers;
use \Yii;
use CArrayDataProvider;
class PluginsController extends Controller
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
        $pm = App()->pluginManager;
        foreach ($pm->scanPlugins() as $pluginConfig) {
            if ($pluginConfig->id === $id) {
                if ($pm->enablePlugin($id)) {
                    App()->user->setFlash('success', gT("Plugin activated."));
                } else {
                    App()->user->setFlash('error', gT("Plugin activation failed."));
                }
            }
        }
        
        $this->redirect(['plugins/']);
    }

    public function actionConfigure($id)
    {
        if (null !== $plugin = App()->pluginManager->getPlugin($id)) {
            $pluginConfig = \ls\pluginmanager\PluginConfig::findAll(false)[$id];
            $plugin = App()->pluginManager->loadPlugin($pluginConfig);

            if (App()->request->isPostRequest) {
                $plugin->saveSettings(App()->request->getPost($plugin->id));
            }
            $this->render('configure', ['plugin' => $plugin]);
        } else {
            throw new \CHttpException(404, "Plugin not found.");
        }
        
    }

    public function actionDeactivate($id)
    {
        if ($id === App()->authManager->authorizationPlugin->id) {
            App()->user->setFlash('error', "Cannot disable currently active authorization plugin.");
        } elseif (in_array($id, SettingGlobal::get('authenticationPlugins', []))) {
            App()->user->setFlash('error', "Cannot disable currently active authentication plugin.");
        }
        
        $pm = App()->pluginManager;
        foreach ($pm->scanPlugins() as $pluginConfig) {
            if ($pluginConfig->id === $id) {
                if ($pm->disablePlugin($id)) {
                    App()->user->setFlash('success', gT("Plugin deactivated."));
                } else {
                    App()->user->setFlash('error', gT("Plugin deactivation failed."));
                }
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
        $plugins = new CArrayDataProvider(array_values($pm->scanPlugins()));
        return $this->render('index', [
            'plugins' => $plugins, 
            'authorizers' => $pm->getAuthorizers(), 
            'authenticators' => $pm->getAuthenticators(),
            'modules' => App()->getModules()
        ]);
    }

    public function filters()
    {
        return array_merge(parent::filters(), ['accessControl']);
    }

}

?>
