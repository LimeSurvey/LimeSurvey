<?php

/**
 * Web server authentication
 *
 * Logs the user in with the username the web server passes in $_SERVER (REMOTE_USER by default),
 * e.g. from Basic, Kerberos or Shibboleth authentication applied to /index.php/admin.
 * Every administration controller is served under that URL prefix (see AdminControllerInterface),
 * so protecting /index.php/admin in the web server covers the whole administration.
 * The web server identity is checked again before every action: when it no longer matches
 * the logged-in user, the session is ended and the login page is shown.
 */
class Authwebserver extends LimeSurvey\PluginManager\AuthPluginBase
{
    protected $storage = 'DbStorage';

    protected static $description = 'Core: Webserver authentication';
    protected static $name = 'Webserver';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    protected $settings = array(
        'strip_domain' => array(
            'type' => 'boolean',
            'label' => 'Strip domain part (DOMAIN\\USER or USER@DOMAIN)',
            'default' => '0',
        ),
        'serverkey' => array(
            'type' => 'string',
            'label' => 'Key to use for username e.g. PHP_AUTH_USER, LOGON_USER, REMOTE_USER. See phpinfo in global settings.',
            'default' => 'REMOTE_USER',
        ),
        'is_default' => array(
            'type' => 'boolean',
            'label' => 'Default authentication method (disables the default LimeSurvey DB authentication if activated).',
            'default' => '1',
        )
    );

    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('getGlobalBasePermissions');
        $this->subscribe('beforeLogin');
        $this->subscribe('newUserSession');
        $this->subscribe('beforeControllerAction');
    }

    /**
     * Add AuthLDAP Permission to global Permission
     * @return void
     */
    public function getGlobalBasePermissions()
    {
        $this->getEvent()->append('globalBasePermissions', array(
            'auth_webserver' => array(
                'create' => false,
                'update' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Use web server authentication"),
                'description' => gT("Use web server authentication"),
                'img' => 'usergroup'
            ),
        ));
    }

    /**
     * Username the web server authenticated, mapped to the LimeSurvey username
     *
     * Reads the configured server key, strips the domain part when wanted
     * and applies the auth_webserver_user_map config setting.
     *
     * @return string|null Null when the web server did not pass a username (key missing or empty, e.g. IIS LOGON_USER on anonymous requests)
     */
    protected function getWebserverUserName()
    {
        $serverKey = $this->get('serverkey');
        if (empty($serverKey) || !isset($_SERVER[$serverKey]) || $_SERVER[$serverKey] === '') {
            return null;
        }
        $sUser = $_SERVER[$serverKey];
        // Only strip domain part when desired
        if ($this->get('strip_domain', null, null, false)) {
            if (strpos((string) $sUser, "\\") !== false) {
                // Get username for DOMAIN\USER
                $sUser = substr((string) $sUser, strrpos((string) $sUser, "\\") + 1);
            } elseif (strpos((string) $sUser, "@") !== false) {
                // Get username for USER@DOMAIN
                $sUser = substr((string) $sUser, 0, strrpos((string) $sUser, "@"));
            }
        }
        $aUserMappings = $this->api->getConfigKey('auth_webserver_user_map', array());
        if (isset($aUserMappings[$sUser])) {
            $sUser = $aUserMappings[$sUser];
        }
        return $sUser;
    }

    public function beforeLogin()
    {
        // normal login through webserver authentication
        $sUser = $this->getWebserverUserName();
        if ($sUser !== null) {
            $authEvent = $this->getEvent();
            $oUser = $this->api->getUserByName($sUser);
            if (
                ($oUser && Permission::model()->hasGlobalPermission('auth_webserver', 'read', $oUser->uid))
                || (!$oUser && $this->api->getConfigKey('auth_webserver_autocreate_user'))
            ) {
                $this->setUsername($sUser);
                $this->setAuthPlugin($authEvent); // This plugin handles authentication, halt further execution of auth plugins
                return;
            }
        }
        $serverKey = $this->get('serverkey');
        if (!empty($serverKey) && $this->get('is_default', null, null, $this->settings['is_default']['default'])) {
            throw new CHttpException(401, 'Wrong credentials for LimeSurvey administration.');
        }
    }

    public function newUserSession()
    {
        // Do nothing if this user is not Authwebserver type
        $identity = $this->getEvent()->get('identity');
        if ($identity->plugin != 'Authwebserver') {
            return;
        }
        /* @var $authEvent LimeSurvey\PluginManager\PluginEvent */
        $authEvent = $this->getEvent();
        /* @var $identity LSUserIdentity */
        $sUser = $this->getUserName();
        $oUser = $this->api->getUserByName($sUser);
        if (is_null($oUser)) {
            if (function_exists("hook_get_auth_webserver_profile")) {
                // If defined this function returns an array
                // describing the default profile for this user
                $aUserProfile = hook_get_auth_webserver_profile($sUser);
            } elseif ($this->api->getConfigKey('auth_webserver_autocreate_user')) {
                $aUserProfile = $this->api->getConfigKey('auth_webserver_autocreate_profile');
            }
        } else {
            if (Permission::model()->hasGlobalPermission('auth_webserver', 'read', $oUser->uid)) {
                $this->setAuthSuccess($oUser, $authEvent);
                return;
            } else {
                $this->setAuthFailure(self::ERROR_AUTH_METHOD_INVALID, gT('Web server authentication method is not allowed for this user'), $authEvent);
                return;
            }
        }

        if ($this->api->getConfigKey('auth_webserver_autocreate_user') && isset($aUserProfile) && is_null($oUser)) {
            // user doesn't exist but auto-create user is set
            $oUser = new User();
            $oUser->users_name = $sUser;
            $oUser->setPassword(createPassword()); // needed ?
            $oUser->full_name = $aUserProfile['full_name'];
            $oUser->parent_id = 1;
            $oUser->lang = $aUserProfile['lang'];
            $oUser->email = $aUserProfile['email'];

            if ($oUser->save()) {
                Permission::setPermissions($oUser->uid, 0, 'global', $this->api->getConfigKey('auth_webserver_autocreate_permissions'), true);
                Permission::model()->setGlobalPermission($oUser->uid, 'auth_webserver');

                // read again user from newly created entry
                $this->setAuthSuccess($oUser, $authEvent);
                return;
            } else {
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID, gT('Unable to create user'), $authEvent);
            }
        }
    }

    /**
     * End the session when the web server now authenticates somebody else
     *
     * Runs before every controller action. Only sessions opened through this plugin are checked,
     * and only when the web server passes a username. A different username means the identity
     * behind the web server session changed, so the LimeSurvey session is ended and the user is
     * sent to the login page, where the current web server identity is logged in.
     *
     * @return void
     */
    public function beforeControllerAction()
    {
        if (!$this->hasWebserverUserChanged()) {
            return;
        }
        App()->user->logout();
        $this->getEvent()->set('run', false);
        App()->getRequest()->redirect(App()->createUrl('/admin/authentication/sa/login'), false);
    }

    /**
     * Whether the session was opened through this plugin but the web server now names another user
     *
     * @return bool
     */
    public function hasWebserverUserChanged()
    {
        if (App()->user->isGuest || App()->user->getState('plugin') !== get_class($this)) {
            return false;
        }
        $sUser = $this->getWebserverUserName();
        if ($sUser === null) {
            return false;
        }
        $oUser = $this->api->getUserByName($sUser);
        return $oUser === null || (int) $oUser->uid !== (int) App()->user->getId();
    }

    /**
     * Modified getPluginSettings to check for invalid settings
     *
     * @param boolean $getValues
     * @return array
     */
    public function getPluginSettings($getValues = true)
    {
        $settings = parent::getPluginSettings($getValues);

        if (!empty($settings['serverkey']) && !empty($settings['serverkey']['current'])) {
            if (!isset($_SERVER[$settings['serverkey']['current']])) {
                $settings['serverkey']['help'] = App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                    'tag' => 'p',
                    'text' => gT(
                        "The server key is not currently set. If you set this plugin as default you will not be able to log in again."
                    ),
                    'type' => 'info',
                ], true);
            }
        }

        if (App()->getUrlManager()->getUrlFormat() !== CUrlManager::PATH_FORMAT) {
            $settings = [
                'urlformat_warning' => [
                    'type' => 'info',
                    'content' => App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                        'tag' => 'p',
                        'text' => gT(
                            "Web server authentication protects a URL path, so the URL format must be \"path\" (urlFormat in config.php). With the current \"get\" format the administration is not covered by the web server's authentication."
                        ),
                        'type' => 'warning',
                    ], true),
                ],
            ] + $settings;
        }

        return $settings;
    }
}
