<?php
namespace ls\core\plugins;
use ls\pluginmanager\PluginBase;
use \ls\pluginmanager\PluginEvent;
use \Yii;
use \CHtml;

class AuthDb extends PluginBase implements \ls\pluginmanager\AuthenticationPluginInterface
{
    use \ls\pluginmanager\InternalUserDbTrait;
    protected $storage = 'DbStorage';
    protected $_onepass = null;


    /**
     * This function performs username password configuration.
     * @param \CHttpRequest $request
     */
    public function authenticate(\CHttpRequest $request) {
        if ($request->isPostRequest) {
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $user = \ls\models\User::model()->findByAttributes(['users_name' => $username]);
            if (isset($user) && $user->validatePassword($password)) {
                return $user;
            }
        }
    }
    
}
