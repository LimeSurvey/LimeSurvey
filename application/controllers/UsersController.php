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
        // Get all active auth plugins.
        $event = new PluginEvent('newLoginForm');
        $event->dispatch();
        $loginForms = $event->get('forms');
        return $this->render('login', ['loginForms' => $loginForms]);
    }

}

?>
