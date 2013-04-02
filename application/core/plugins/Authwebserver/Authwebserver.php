<?php
class Authwebserver extends PluginBase
{
    protected $storage = 'DbStorage';    
    
    static protected $description = 'Core: Webserver authentication';
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeLogin');
        $this->subscribe('newUserSession');
    }

    public function beforeLogin(PluginEvent $event)
    {
        /* @var $identity LSUserIdentity */
        $identity = $event->get('identity');
        
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
            if (strpos($sUser,"\\")!==false) {
                $sUser = substr($sUser, strrpos($sUser, "\\")+1);
            }
            
            $aUserMappings=$this->api->getConfigKey('auth_webserver_user_map', array());
            if (isset($aUserMappings[$sUser])) 
            {
               $sUser = $aUserMappings[$sUser];
            }
            $identity->username = $sUser;
            $identity->plugin = get_class($this);
            $event->stop();
        }
        
        $event->set('identity', $identity);
    }
    
    public function newUserSession(PluginEvent $event)
    {
        /* @var $identity LSUserIdentity */
        $identity = $event->getSender();
        $sUser = $identity->username;
        
        $oUser=User::model()->findByAttributes(array('users_name'=>$sUser));
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
            $identity->id = $oUser->uid;
            $identity->user = $oUser;
            $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_NONE));
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
                $identity->id = $oUser->uid;
                $identity->user = $oUser;                    
                $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_NONE));                   
            }
            else
            {
                $event->set('result', new LSAuthResult(LSUserIdentity::ERROR_USERNAME_INVALID));
            }

        }
        
    }
    
    
}