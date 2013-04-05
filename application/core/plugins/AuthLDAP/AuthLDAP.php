<?php
class AuthLDAP extends AuthPluginBase
{
    protected $storage = 'DbStorage';
       
    static protected $description = 'Core: Basic LDAP authentication';
    static protected $name = 'LDAP';
    
    protected $settings = array(
        'server' => array(
            'type' => 'string',
            'label' => 'Ldap server e.g. ldap://ldap.mydomain.com'
        ),
        'domainsuffix' => array(
            'type' => 'string',
            'label' => 'Domain suffix for username e.g. @mydomain.com'
        ),
        'is_default' => array(
            'type' => 'boolean',
            'label' => 'Should this plugin present itself as default authentication method?'
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
        $this->subscribe('beforeDeactivate');
    }

    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);

        // Optionally set a custom error message.
        $this->getEvent()->set('message', gT('Core plugin can not be disabled.'));
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
        
        if ($user === null)
        {
            // If the user doesnt exist ín th eLS database, he can not login
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            return;
        }
        
        // Get configuration settings:
        $ldapserver = $this->get('server');        
        $domain     = $this->get('domainsuffix');;

        // Try to connect
        $ldapconn = ldap_connect($ldapserver);
        if (false == $ldapconn) {
            $this->setAuthFailure(1, gT('Could not connect to LDAP server.'));
            return;
        }

        if($ldapconn) {
            // binding to ldap server
            $ldapbind = ldap_bind($ldapconn, $username.$domain, $password);
            // verify binding
            if (!$ldapbind) {
                $this->setAuthFailure(100, ldap_error($ldapconn));
                ldap_close($ldapconn); // all done? close connection
                return;
            }
            ldap_close($ldapconn); // all done? close connection
        }

        $this->setAuthSuccess($user);
    }
}