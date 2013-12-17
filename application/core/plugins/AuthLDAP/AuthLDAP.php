<?php
class AuthLDAP extends AuthPluginBase
{
    protected $storage = 'DbStorage';
       
    static protected $description = 'Core: Basic LDAP authentication';
    static protected $name = 'LDAP';
    
    /**
     * Can we autocreate users? For the moment this is disabled, will be moved 
     * to a setting when we have more robust user creation system.
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
            $this->setUsername( $request->getPost('user'));
            $this->setPassword($request->getPost('password'));
        }
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
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            return;
        }
        
        // Get configuration settings:
        $ldapserver = $this->get('server');
        $ldapport   = $this->get('ldapport');
        $ldapver    = $this->get('ldapversion');
        $suffix     = $this->get('domainsuffix');
        $prefix     = $this->get('userprefix');
        
        if (empty($ldapport)) {
            $ldapport = 389;
        }

        // Try to connect
        $ldapconn = ldap_connect($ldapserver, (int) $ldapport);
        if (false == $ldapconn) {
            $this->setAuthFailure(1, gT('Could not connect to LDAP server.'));
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
        $ldapbind = ldap_bind($ldapconn, $prefix . $username . $suffix, $password);
        // verify binding
        if (!$ldapbind) {
            $this->setAuthFailure(100, ldap_error($ldapconn));
            ldap_close($ldapconn); // all done? close connection
            return;
        }        
        
        // Authentication was successful, now see if we have a user or that we should create one
         if (is_null($user)) {
            if ($this->autoCreate === true)  {
                /*
                 * Dispatch the newUserLogin event, and hope that after this we can find the user
                 * this allows users to create their own plugin for handling the user creation
                 * we will need more methods to pass username, rdn and ldap connection.
                 */                
                $this->pluginManager->dispatchEvent(new PluginEvent('newUserLogin', $this));
                
                // Check ourselves, we do not want fake resonses from a plugin
                $user = $this->api->getUserByName($username);
            }
            
            if (is_null($user)) {
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                ldap_close($ldapconn); // all done? close connection
                return;
            }
        }
        
        ldap_close($ldapconn); // all done? close connection
        
        // If we made it here, authentication was a success and we do have a valid user
        $this->setAuthSuccess($user);
    }
}