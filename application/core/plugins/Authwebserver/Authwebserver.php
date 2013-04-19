<?php
class Authwebserver extends AuthPluginBase
{
    protected $storage = 'DbStorage';    
    
    static protected $description = 'Core: Webserver authentication';
    static protected $name = 'Webserver';
    
    protected $settings = array(
        'strip_domain' => array(
            'type' => 'checkbox',
            'label' => 'Strip domain part (DOMAIN\\USER or USER@DOMAIN)'
        )
    );
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeLogin');
        $this->subscribe('newUserSession');
    }

    public function beforeLogin()
    {       
        // normal login through webserver authentication    
        if (isset($_SERVER['PHP_AUTH_USER'])||isset($_SERVER['LOGON_USER']) ||isset($_SERVER['REMOTE_USER']))    
        {
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $sUser=$_SERVER['PHP_AUTH_USER'];
            }
            elseif (isset($_SERVER['REMOTE_USER'])) {
                $sUser=$_SERVER['REMOTE_USER'];
            } else {
                $sUser = $_SERVER['LOGON_USER'];
            }
            
            // Only strip domain part when desired
            if ($this->get('strip_domain', null, null, false)) {
                if (strpos($sUser,"\\")!==false) {
                    // Get username for DOMAIN\USER
                    $sUser = substr($sUser, strrpos($sUser, "\\")+1);
                } elseif (strpos($sUser,"@")!==false) {
                    // Get username for USER@DOMAIN
                    $sUser = substr($sUser, 0, strrpos($sUser, "@"));
                }
            }
            
            $aUserMappings=$this->api->getConfigKey('auth_webserver_user_map', array());
            if (isset($aUserMappings[$sUser])) 
            {
               $sUser = $aUserMappings[$sUser];
            }
            $this->setUsername($sUser);
            $this->setAuthPlugin(); // This plugin handles authentication, halt further execution of auth plugins
        }
    }
    
    public function newUserSession()
    {
        /* @var $identity LSUserIdentity */
        $sUser = $this->getUserName();
        
        $oUser = $this->api->getUserByName($sUser);
        if (is_null($oUser))
        {
            if (function_exists("hook_get_auth_webserver_profile"))
            {
                // If defined this function returns an array
                // describing the default profile for this user
                $aUserProfile = hook_get_auth_webserver_profile($sUser);
            }
            elseif ($this->api->getConfigKey('auth_webserver_autocreate_user'))
            {
                $aUserProfile=$this->api->getConfigKey('auth_webserver_autocreate_profile');
            }
        } else {
            $this->setAuthSuccess($oUser);
            return;
        }

        if ($this->api->getConfigKey('auth_webserver_autocreate_user') && isset($aUserProfile) && is_null($oUser))
        { // user doesn't exist but auto-create user is set
            $oUser=new User;
            $oUser->users_name=$sUser;
            $oUser->password=hash('sha256', createPassword());
            $oUser->full_name=$aUserProfile['full_name'];
            $oUser->parent_id=1;
            $oUser->lang=$aUserProfile['lang'];
            $oUser->email=$aUserProfile['email'];
            $oUser->create_survey=$aUserProfile['create_survey'];
            $oUser->create_user=$aUserProfile['create_user'];
            $oUser->delete_user=$aUserProfile['delete_user'];
            $oUser->superadmin=$aUserProfile['superadmin'];
            $oUser->configurator=$aUserProfile['configurator'];
            $oUser->manage_template=$aUserProfile['manage_template'];
            $oUser->manage_label=$aUserProfile['manage_label'];

            if ($oUser->save())
            {
                $aTemplates=explode(",",$aUserProfile['templatelist']);
                foreach ($aTemplates as $sTemplateName)
                {
                    $oRecord=new Templates_rights;
                    $oRecord->uid = $oUser->uid;
                    $oRecord->folder = trim($sTemplateName);
                    $oRecord->use = 1;
                    $oRecord->save();
                }

                // read again user from newly created entry
                $this->setAuthSuccess($oUser);
                return;
            }
            else
            {
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            }

        }
        
    }
    
    
}