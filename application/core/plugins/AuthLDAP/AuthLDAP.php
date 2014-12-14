<?php
class AuthLDAP extends AuthPluginBase
{
    protected $storage = 'DbStorage';

    static protected $description = 'Core: LDAP authentication';
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
            'label' => 'Ldap server e.g. ldap://ldap.mydomain.com or ldaps://ldap.mydomain.com'
            ),
        'ldapport' => array(
            'type' => 'string',
            'label' => 'Port number (default when omitted is 389)'
            ),
        'ldapversion' => array(
            'type' => 'select',
            'label' => 'LDAP version',
            'options' => array('2' => 'LDAPv2', '3'  => 'LDAPv3'),
            'default' => '2',
            'submitonchange'=> true
            ),
        'ldapoptreferrals' => array(
            'type' => 'boolean',
            'label' => 'Select true if referrals must be followed (use false for ActiveDirectory)',
            'default' => '0'
            ),
        'ldaptls' => array(
            'type' => 'boolean',
            'label' => 'Check to enable Start-TLS encryption When using LDAPv3',
            'default' => '0'
            ),
        'ldapmode' => array(
            'type' => 'select',
            'label' => 'Select how to perform authentication.',
            'options' => array("simplebind" => "Simple bind", "searchandbind" => "Search and bind"),
            'default' => "simplebind",
            'submitonchange'=> true
            ),
        'userprefix' => array(
            'type' => 'string',
            'label' => 'Username prefix cn= or uid=',
            ),
        'domainsuffix' => array(
                'type' => 'string',
                'label' => 'Username suffix e.g. @mydomain.com or remaining part of ldap query'
                ),
        'searchuserattribute' => array(
                'type' => 'string',
                'label' => 'Attribute to compare to the given login can be uid, cn, mail, ...'
                ),
        'usersearchbase' => array(
                'type' => 'string',
                'label' => 'Base DN for the user search operation'
                ),
        'extrauserfilter' => array(
                'type' => 'string',
                'label' => 'Optional extra LDAP filter to be ANDed to the basic (searchuserattribute=username) filter. Don\'t forget the outmost enclosing parentheses'
                ),
        'binddn' => array(
                'type' => 'string',
                'label' => 'Optional DN of the LDAP account used to search for the end-user\'s DN. An anonymous bind is performed if empty.'
                ),
        'bindpwd' => array(
                'type' => 'string',
                'label' => 'Password of the LDAP account used to search for the end-user\'s DN if previoulsy set.'
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

    /**
     * Modified getPluginSettings since we have a select box that autosubmits
     * and we only want to show the relevant options.
     * 
     * @param boolean $getValues
     * @return array
     */
    public function getPluginSettings($getValues = true)
    {
        $aPluginSettings = parent::getPluginSettings($getValues);
        if ($getValues) {
            $ldapmode = $aPluginSettings['ldapmode']['current'];
            $ldapver = $aPluginSettings['ldapversion']['current'];
            
            // If it is a post request, it could be an autosubmit so read posted
            // value over the saved value
            if (App()->request->isPostRequest) {
                $ldapmode = App()->request->getPost('ldapmode', $ldapmode);
                $aPluginSettings['ldapmode']['current'] = $ldapmode;
                $ldapver = App()->request->getPost('ldapversion', $ldapver);
                $aPluginSettings['ldapversion']['current'] = $ldapver;
            }
            
            if ($ldapver == '2' ) {
               unset($aPluginSettings['ldaptls']); 
            }

            if ($ldapmode == 'searchandbind') {
                // Hide simple settings
                unset($aPluginSettings['userprefix']);
                unset($aPluginSettings['domainsuffix']);

            } else {
                // Hide searchandbind settings
                unset($aPluginSettings['searchuserattribute']);
                unset($aPluginSettings['usersearchbase']);
                unset($aPluginSettings['extrauserfilter']);
                unset($aPluginSettings['binddn']);
                unset($aPluginSettings['bindpwd']);
                unset($aPluginSettings['ldapoptreferrals']);
            }
        }
        
        return $aPluginSettings;
    }

    public function newUserSession()
    {
        // Do nothing if this user is not Authdb type
        $identity = $this->getEvent()->get('identity');
        if ($identity->plugin != 'AuthLDAP')
        {
            return;
        }

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

        if (empty($password))
        {
            // If password is null or blank reject login
            // This is necessary because in simple bind ldap server authenticates with blank password
            $this->setAuthFailure(self::ERROR_PASSWORD_INVALID);
            return;
        }

        // Get configuration settings:
        $ldapserver 		= $this->get('server');
        $ldapport   		= $this->get('ldapport');
        $ldapver    		= $this->get('ldapversion');
        $ldaptls    		= $this->get('ldaptls');
        $ldapoptreferrals	= $this->get('ldapoptreferrals');
        $ldapmode    		= $this->get('ldapmode');
        $suffix     		= $this->get('domainsuffix');
        $prefix     		= $this->get('userprefix');
        $searchuserattribute    = $this->get('searchuserattribute');
        $extrauserfilter    	= $this->get('extrauserfilter');
        $usersearchbase		= $this->get('usersearchbase');
        $binddn     		= $this->get('binddn');
        $bindpwd     		= $this->get('bindpwd');



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
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, $ldapoptreferrals);

        if (!empty($ldaptls) && $ldaptls == '1' && $ldapver == 3 && preg_match("/^ldaps:\/\//", $ldapserver) == 0 )
        {
            // starting TLS secure layer
            if(!ldap_start_tls($ldapconn))
            {
                $this->setAuthFailure(100, ldap_error($ldapconn));
                ldap_close($ldapconn); // all done? close connection
                return;
            }
        }

        if (empty($ldapmode) || $ldapmode=='simplebind')
        {
            // in simple bind mode we know how to construct the userDN from the username
            $ldapbind = @ldap_bind($ldapconn, $prefix . $username . $suffix, $password);
        }
        else
        {
            // in search and bind mode we first do a LDAP search from the username given
            // to foind the userDN and then we procced to the bind operation
            if (empty($binddn))
            {
                // There is no account defined to do the LDAP search, 
                // let's use anonymous bind instead
                $ldapbindsearch = @ldap_bind($ldapconn);
            }
            else
            {
                // An account is defined to do the LDAP search, let's use it
                $ldapbindsearch = @ldap_bind($ldapconn, $binddn, $bindpwd);
            }
            if (!$ldapbindsearch) {
                $this->setAuthFailure(100, ldap_error($ldapconn));
                ldap_close($ldapconn); // all done? close connection
                return;
            }        
            // Now prepare the search fitler
            if ( $extrauserfilter != "")
            {
                $usersearchfilter = "(&($searchuserattribute=$username)$extrauserfilter)";
            }
            else
            {
                $usersearchfilter = "($searchuserattribute=$username)";
            }
            // Search for the user
            $dnsearchres = ldap_search($ldapconn, $usersearchbase, $usersearchfilter, array($searchuserattribute));
            $rescount=ldap_count_entries($ldapconn,$dnsearchres);
            if ($rescount == 1)
            {
                $userentry=ldap_get_entries($ldapconn, $dnsearchres);
                $userdn = $userentry[0]["dn"];
            }
            else
            {
                // if no entry or more than one entry returned
                // then deny authentication
                $this->setAuthFailure(100, ldap_error($ldapconn));
                ldap_close($ldapconn); // all done? close connection
                return;
            }

            // binding to ldap server with the userDN and privided credentials
            $ldapbind = @ldap_bind($ldapconn, $userdn, $password);
        }

        // verify user binding
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

                // Check ourselves, we do not want fake responses from a plugin
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
