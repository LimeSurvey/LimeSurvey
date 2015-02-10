<?php
use ls\pluginmanager\PluginEvent;
class UsersController extends LSYii_Controller
{

    public $layout = 'minimal';

   
    public function accessRules()
    {
        // Note the order; rules are numerically indexed and we want to
        // parents rules to be executed only if ours dont apply.
        return array_merge([
            ['allow' ,'actions' => 'login'],
            ['allow' , 'actions' => 'logout', 'users' => '@'],
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
            }
        } else {
            // Get all active auth plugins.
            $forms = array_map(function(\ls\pluginmanager\AuthPluginBase $authenticator) {
                return $authenticator->getLoginSettings();
            }, $authenticators);
            return $this->render('login', ['loginForms' => $forms]);
        }
    }
    
    public function actionLogout() {
        (new PluginEvent('beforeLogout'))->dispatch();
        

        App()->user->logout();
       
        /* Adding afterLogout event */
        (new PluginEvent('afterLogout'))->dispatch();
        $this->redirect(['admin/']);
    }

}

?>
