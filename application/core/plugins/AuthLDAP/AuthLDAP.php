<?php
class AuthLDAP extends AuthPluginBase
{
    protected $storage = 'DbStorage';
       
    static protected $description = 'Core: Basic LDAP authentication';
    static protected $name = 'LDAP';
    
    /**
     * Can we autocreate users? For the moment this is disabled.
     * The user creation system is mostly copied from Authwebserver.
     * This code was tested with openLDAP.
     * 
     * More testing is desired and for a production server the less informative
     * error messages may be desired (currently commented out).
     * 
     * @var boolean
     */
    protected $autoCreate = false;
    
    protected $settings = array(
        'server' => array(
            'type' => 'string',
            'label' => 'Ldap server e.g. ldap://ldap.mydomain.com'
        ),
        'ldapport' => array(
            'type' => 'string',
            'label' => 'Port number (default when omitted is 389)'
        ),
        'ldapversion' => array(
            'type' => 'string',
            'label' => 'LDAP version (LDAPv2 = 2), e.g. 3'
        ),
        'userprefix' => array(
            'type' => 'string',
            'label' => 'Username prefix cn= or uid='
        ),
        'domainsuffix' => array(
            'type' => 'string',
            'label' => 'Username suffix e.g. @mydomain.com or remaining part of ldap query'
        ),
        'is_default' => array(
            'type' => 'checkbox',
            'label' => 'Check to make default authentication method'
        ),
        'binddn' => array(
            'type' => 'string',
            'label' => 'Bind DN (optional, only if different from user DN)'
        ),
        'basedn' => array(
            'type' => 'string',
            'label' => 'Base DN (optional, if bind DN is used only)'
        ),
        'bindpassword' => array(
            'type' => 'password',
            'label' => 'Password for bind DN (optional, if bind DN is used only)'
        ),
        'fullnameattribute' => array(
            'type' => 'string',
            'label' => 'LDAP attribute for full name, e.g. "cn".'
        ),
        'emailattribute' => array(
            'type' => 'string',
            'label' => 'LDAP attribute for email, e.g. "email".'
        )
    );
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeLogin');
        $this->subscribe('newLoginForm');
        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('newUserLogin');
        $this->subscribe('newUserSession');
    }
    
    public function beforeLogin()
    {
        if ($this->get('is_default', null, null, false) == true) { 
            // This is configured to be the default login method
            $this->getEvent()->set('default', get_class($this));
        }
    }
       
    public function newLoginForm()
    {
        $this->getEvent()->getContent($this)
             ->addContent(CHtml::tag('li', array(), "<label for='user'>"  . gT("Username") . "</label><input name='user' id='user' type='text' size='40' maxlength='40' value='' />"))
             ->addContent(CHtml::tag('li', array(), "<label for='password'>"  . gT("Password") . "</label><input name='password' id='password' type='password' size='40' maxlength='40' value='' />"));
    }
    
    public function afterLoginFormSubmit()
    {
        // Here we handle post data        
        $request = $this->api->getRequest();
        if ($request->getIsPostRequest()) {
            $this->setUsername($request->getPost('user'));
            $this->setPassword($request->getPost('password'));
        }
    }
    
    public function newUserLogin($username, $fullname, $email){
        // user doesn't exist but auto-create user is set,
        // code largely taken from authwebserver
        $oUser=new User;
        $oUser->users_name = $username;
        $oUser->password = hash('sha256', createPassword()); 
        // what password is hashed?, do not care for now since we use the ldap password 
        $oUser->full_name = $fullname;
        $oUser->parent_id = 1;
        $oUser->lang = "en";
        $oUser->email = $email;

        //if ($oUser->save()){
            //error_log("creating user success");
            //$permission=new Permission;
            //$permission->setPermissions($oUser->uid, 0, 'global', $this->api->getConfigKey('auth_webserver_autocreate_permissions'), true);
        //} else {
            //error_log("creating user failure");
        //}
    }
    
    public function newUserSession()
    {
        // Here we do the actual authentication       
        $username = $this->getUsername();
        $password = $this->getPassword();
        
        $user = $this->api->getUserByName($username);
        
        if ($user === null && $this->autoCreate === false)
        {
            // If the user doesnt exist ín the LS database, he can not login
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID, "User does not exist and no autocreate");
            //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            return;
        }
        
        // Get configuration settings:
        $ldapserver        = $this->get('server');
        $ldapport          = $this->get('ldapport');
        $ldapver           = $this->get('ldapversion');
        $suffix            = $this->get('domainsuffix');
        $prefix            = $this->get('userprefix');
        $binddn            = $this->get('binddn');
        $basedn            = $this->get('basedn');
        $bindpassword      = $this->get('bindpassword');
        $fullnameattribute = $this->get('fullnameattribute');
        $emailattribute    = $this->get('emailattribute');
        
        if (empty($ldapport)) {
            $ldapport = 389;
        }

        // Try to connect
        $ldapconn = ldap_connect($ldapserver, (int) $ldapport);
        if (false == $ldapconn) {
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID, gT('Could not connect to LDAP server.'));
            return;
        }
        
        // using LDAP version
        if ($ldapver === null)
        {
            // If the version hasn't been set, default = 2
            $ldapver = 2;
        }
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, $ldapver);

        // binding to ldap server
        if (empty($binddn)){
            $ldapbind = ldap_bind($ldapconn, $prefix . $username . $suffix, $password);
        } else {
            $ldapbind = ldap_bind($ldapconn, $binddn, $bindpassword);
        }

        // verify binding
        if (!$ldapbind) {
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID, ldap_error($ldapconn));
            ldap_close($ldapconn); // all done? close connection
            return;
        }        

        $result = ldap_search($ldapconn, $basedn, $prefix . $username . $suffix);
        // wether this is successful or not is only relevant when using bind_dn or creating this user
        
        // if a separate binddn is given the password check procedure is more complicated:
        if (!empty($binddn)){
            // find dn of user
            if ($result==-1){
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID, "User not found: ".ldap_error($ldapconn));
                //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                ldap_close($ldapconn); // all done? close connection
                return;
            }
            if (ldap_count_entries($ldapconn, $result)!=1){
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID, "User not uniquely found with base DN: '".$basedn."', search: '". $prefix . $username . $suffix, "'" );
                //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                ldap_close($ldapconn); // all done? close connection
                return;
            }
            $entry = ldap_first_entry($ldapconn, $result) ;
            $dn = ldap_get_dn($ldapconn, $entry);
            if (!$dn){
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID, "User DN not found");
                //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                ldap_close($ldapconn); // all done? close connection
                return;
            }
            
            // verify password of given DN
            $r = ldap_compare($ldapconn, $dn, "userpassword", $password);
            if (!$r){
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID, ldap_error($ldapconn)."password (".$dn."): ".$password);
                //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                ldap_close($ldapconn); // all done? close connection
                return;
            }
        } else {
            // get user entry, but do not return on failure, it is just for full name and email
            if ($result==-1)
                ldap_close($ldapconn);
                break;
            if (ldap_count_entries($ldapconn, $result)!=1)
                ldap_close($ldapconn);
                break;
            $entry = ldap_first_entry($ldapconn, $result) ;
        }
        
        // Authentication was successful, now see if we have a user or that we should create one
        if (is_null($user)) {
            if ($this->autoCreate === true)  {
                $fullname = "John Doe";
                $email = "john.doe@example.com";
                /*
                 * Dispatch the newUserLogin event, and hope that after this we can find the user
                 * this allows users to create their own plugin for handling the user creation
                 * we will need more methods to pass username, rdn and ldap connection.
                 */
                // the $entry retrieved from de ldap server still exists, use it to get email and full name
                //$this->pluginManager->dispatchEvent(new PluginEvent('newUserLogin', $this));
                if($entry){
                    $attributes = ldap_get_attributes($ldapconn, $entry);
                    if($attributes[$fullnameattribute]["count"])
                         $fullname = $attributes[$fullnameattribute][0];
                        
                    if($attributes[$emailattribute]["count"])
                         $email = $attributes[$emailattribute][0];
                    
                    $this->newUserLogin($username, $fullname, $email);
                    
                    // Check ourselves, we do not want fake resonses from a plugin
                    $user = $this->api->getUserByName($username);
                    if (is_null($user)) {
                        $this->setAuthFailure(self::ERROR_USERNAME_INVALID, "Could not create user.");
                        //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                        return;
                    }
                } else {
                    $this->setAuthFailure(self::ERROR_USERNAME_INVALID, "User not found for creating: ".ldap_error($ldapconn));
                    //$this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                    ldap_close($ldapconn); // all done? close connection
                    return;
                }
            }
        }
        ldap_close($ldapconn); // all done? close connection
        // If we made it here, authentication was a success and we do have a valid user
        $this->setAuthSuccess($user);
    }
}
