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
        $request = Yii::app()->request;
        if ($request->getParam('_logintype') !== null) {
            $plugin = App()->pluginManager->getPlugin($request->getParam('_logintype'));
            if ($plugin instanceof \ls\pluginmanager\AuthPluginBase) {
                $identity = new PluginIdentity($plugin);
                if ($identity->authenticate());
                
                App()->user->login($identity);
                $this->redirect(App()->user->returnUrl);
            }
        } else {
            // Get all active auth plugins.
            $event = new PluginEvent('beforeLoginForm');
            $event->dispatch();
            return $this->render('login', ['loginForms' => $event->get('forms', [])]);
        }
    }
    
    public function actionLogout() {
        App()->user->logout();
    }

}

?>
