<?php
namespace ls\controllers;
use ls\pluginmanager\PluginEvent;
use Yii;
use PluginIdentity;
class UsersController extends Controller
{

    public $layout = 'minimal';

   
    public function accessRules()
    {
        // Note the order; rules are numerically indexed and we want to
        // parents rules to be executed only if ours dont apply.
        return array_merge([
            ['allow' ,'actions' => ['login']],
            ['allow' , 'actions' => ['logout', 'profile'], 'users' => ['@']],
        ], parent::accessRules());
    }

    public function actionLogin() {
        App()->pluginManager->scanPlugins();
        $request = Yii::app()->request; 
        $authenticators = App()->pluginManager->getAuthenticators(true);
        if ($request->getParam('_logintype') !== null && isset($authenticators[$request->getParam('_logintype')])) {
            $plugin = $authenticators[$request->getParam('_logintype')];
            $identity = new PluginIdentity($plugin);
            if ($identity->authenticate()) {
                App()->user->login($identity);
                $this->redirect(App()->user->getReturnUrl(['admin/']));
            } else {
                App()->user->setFlash(\TbHtml::ALERT_COLOR_DANGER, gT("Authentication failed."));
            }
        } 
        // Get all active auth plugins.
        $forms = array_map(function(\ls\pluginmanager\iAuthenticationPlugin $authenticator) {
            return $authenticator->getLoginSettings();
        }, $authenticators);
        return $this->render('login', ['loginForms' => $forms]);
    }
    
    public function actionLogout() {
        (new PluginEvent('beforeLogout'))->dispatch();
        

        App()->user->logout();
       
        /* Adding afterLogout event */
        (new PluginEvent('afterLogout'))->dispatch();
        $this->redirect(['admin/']);
    }
    
    
    public function actionIndex() {
        $this->layout = 'main';
        $this->render('index', ['authenticators' => App()->pluginManager->getAuthenticators(true)]);
    }
    
    public function actionProfile() {
        $this->layout = 'main';
        $user = App()->user->model;
        $prefix = 'profileSettings';
        if (App()->request->isPostRequest) {
            $result = App()->user->model->setSettings(App()->request->getParam($prefix));
        }
        $settings = $user->getSettings();
        if (isset($result)) {
            foreach ($result as $field => $errors) {
                $settings[$field]['errors'] = $errors;
            }
        }
        $this->render('profile', ['user' => $user, 'prefix' => $prefix, 'settings' => $settings]);
    }
    
    public function actionUpdate($id, $plugin) {
        $this->layout = 'main';
        $pluginObject = App()->pluginManager->getPlugin($plugin);
        if (isset($pluginObject) && $pluginObject instanceOf \ls\pluginmanager\iAuthenticationPlugin) {
            $user = $pluginObject->getUser($id);
            if (isset($user)) {
                return $this->render('update', ['user' => $user]);
            }
        }
        throw new \CHttpException(404, "Plugin or user not found.");
    }
    public function filters()
    {
        return array_merge(parent::filters(), ['accessControl']);
    }

}

?>
