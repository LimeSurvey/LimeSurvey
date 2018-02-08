<?php
class AuthLDAP extends LimeSurvey\PluginManager\AuthPluginBase
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
            'label' => 'LDAP server',
            'help' => 'e.g. ldap://ldap.example.com or ldaps://ldap.example.com'
        ),
        'ldapport' => array(
            'type' => 'string',
            'label' => 'Port number',
            'help' => 'Default when omitted is 389',
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
            'help' => 'Check to enable Start-TLS encryption, when using LDAPv3',
            'label' => 'Enable Start-TLS',
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
            'label' => 'Username prefix',
            'help' => 'e.g. cn= or uid=',
            ),
        'domainsuffix' => array(
            'type' => 'string',
            'label' => 'Username suffix',
            'help' => 'e.g. @mydomain.com or remaining part of ldap query',
        ),
        'searchuserattribute' => array(
            'type' => 'string',
            'label' => 'Attribute to compare to the given login can be uid, cn, mail, ...'
        ),
        'usersearchbase' => array(
            'type' => 'string',
            'label' => 'Base DN for the user search operation. Multiple bases may be separated by a semicolon (;)'
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
            'type' => 'password',
            'label' => 'Password of the LDAP account used to search for the end-user\'s DN if previoulsy set.'
        ),
        'mailattribute' => array(
            'type' => 'string',
            'label' => 'LDAP attribute of email address'
        ),
        'fullnameattribute' => array(
            'type' => 'string',
            'label' => 'LDAP attribute of full name'
        ),
        'is_default' => array(
            'type' => 'checkbox',
            'label' => 'Check to make default authentication method'
        ),
        'autocreate' => array(
            'type' => 'checkbox',
            'label' => 'Automatically create user if it exists in LDAP server'
        ),
        'automaticsurveycreation' => array(
            'type' => 'checkbox',
            'label' => 'Grant survey creation permission to automatically created users'
        ),
        'groupsearchbase' => array(
            'type' => 'string',
            'label' => 'Optional base DN for group restriction',
            'help' => 'E.g., ou=Groups,dc=example,dc=com'
        ),
        'groupsearchfilter' => array(
            'type' => 'string',
            'label' => 'Optional filter for group restriction',
            'help' => 'Required if group search base set. E.g. (&(cn=limesurvey)(memberUid=$username)) or (&(cn=limesurvey)(member=$userdn))'
        ),
        'allowInitialUser' => array(
            'type' => 'checkbox',
            'label' => 'Allow initial user to login via LDAP',
        )
    );

    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeActivate');
        $this->subscribe('getGlobalBasePermissions');
        $this->subscribe('beforeHasPermission');
        $this->subscribe('createNewUser');
        $this->subscribe('beforeLogin');
        $this->subscribe('newLoginForm');
        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('remoteControlLogin');

        $this->subscribe('newUserSession');
    }

    /**
     * Check availability of LDAP Apache Module
     *
     * @return void
     */
    public function beforeActivate()
    {
        if (!function_exists("ldap_connect")) {
            $event = $this->getEvent();
            $event->set('success', false);
            $event->set('message', gT("LDAP authentication failed: LDAP PHP module is not available."));
        }
    }

    /**
     * Add AuthLDAP Permission to global Permission
     * @return void
     */
    public function getGlobalBasePermissions()
    {
        $this->getEvent()->append('globalBasePermissions', array(
            'auth_ldap' => array(
                'create' => false,
                'update' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'title' => gT("Use LDAP authentication"),
                'description' => gT("Use LDAP authentication"),
                'img' => 'usergroup'
            ),
        ));
    }

    /**
     * Validation of AuthPermission (for super-admin only)
     * @return void
     */
    public function beforeHasPermission()
    {
        $oEvent = $this->getEvent();
        if ($oEvent->get('sEntityName') != 'global' || $oEvent->get('sPermission') != 'auth_ldap' || $oEvent->get('sCRUD') != 'read') {
            return;
        }
        $iUserId = Permission::getUserId($oEvent->get('iUserID'));
        if ($iUserId == 1) {
            $oEvent->set('bPermission', (bool) $this->get('allowInitialUser'));
        }
    }

    /**
     * Create a LDAP user
     *
     * @return void
     */
    public function createNewUser()
    {
        // Do nothing if the user to be added is not LDAP type
        if (flattenText(Yii::app()->request->getPost('user_type')) != 'LDAP') {
            return;
        }

        $this->_createNewUser(flattenText(Yii::app()->request->getPost('new_user'), false, true));
    }

    /**
     * Create a LDAP user
     *
     * @param string $new_user
     * @return null|integer New user ID
     */
    private function _createNewUser($new_user)
    {
        $oEvent = $this->getEvent();

        // Get configuration settings:
        $ldapmode = $this->get('ldapmode');
        $searchuserattribute = $this->get('searchuserattribute');
        $extrauserfilter = $this->get('extrauserfilter');
        $usersearchbase = $this->get('usersearchbase');
        $binddn         = $this->get('binddn');
        $bindpwd        = $this->get('bindpwd');
        $mailattribute = $this->get('mailattribute');
        $fullnameattribute = $this->get('fullnameattribute');

        // Try to connect
        $ldapconn = $this->createConnection();
        if (!is_resource($ldapconn)) {
            $oEvent->set('errorCode', self::ERROR_LDAP_CONNECTION);
            $oEvent->set('errorMessageTitle', '');
            $oEvent->set('errorMessageBody', $ldapconn['errorMessage']);
            return null;
        }

        if (empty($ldapmode) || $ldapmode == 'simplebind') {
            $oEvent->set('errorCode', self::ERROR_LDAP_MODE);
            $oEvent->set('errorMessageTitle', gT("Failed to add user"));
            $oEvent->set('errorMessageBody', gT("Simple bind LDAP configuration doesn't allow LDAP user creation"));
            return null;
        }

        // Search email address and full name
        if (empty($binddn)) {
            // There is no account defined to do the LDAP search,
            // let's use anonymous bind instead
            $ldapbindsearch = @ldap_bind($ldapconn);
        } else {
            // An account is defined to do the LDAP search, let's use it
            $ldapbindsearch = @ldap_bind($ldapconn, $binddn, $bindpwd);
        }
        if (!$ldapbindsearch) {
            $oEvent->set('errorCode', self::ERROR_LDAP_NO_BIND);
            $oEvent->set('errorMessageTitle', gT('Could not connect to LDAP server.'));
            $oEvent->set('errorMessageBody', gT(ldap_error($ldapconn)));
            ldap_close($ldapconn); // all done? close connection
            return null;
        }
        // Now prepare the search fitler
        if ($extrauserfilter != "") {
            $usersearchfilter = "(&($searchuserattribute=$new_user)$extrauserfilter)";
        } else {
            $usersearchfilter = "($searchuserattribute=$new_user)";
        }
        // Search for the user
        $userentry = false;
        // try each semicolon-separated search base in order
        foreach (explode(";", $usersearchbase) as $usb) {
            $dnsearchres = ldap_search($ldapconn, $usb, $usersearchfilter, array($mailattribute, $fullnameattribute));
            $rescount = ldap_count_entries($ldapconn, $dnsearchres);
            if ($rescount == 1) {
                $userentry = ldap_get_entries($ldapconn, $dnsearchres);
                $new_email = flattenText($userentry[0][strtolower($mailattribute)][0]);
                $new_full_name = flattenText($userentry[0][strtolower($fullnameattribute)][0]);
                break;
            }
        }
        if (!$userentry) {
            $oEvent->set('errorCode', self::ERROR_LDAP_NO_SEARCH_RESULT);
            $oEvent->set('errorMessageTitle', gT('Username not found in LDAP server'));
            $oEvent->set('errorMessageBody', gT('Verify username and try again'));
            ldap_close($ldapconn); // all done? close connection
            return null;
        }

        if (!validateEmailAddress($new_email)) {
            $oEvent->set('errorCode', self::ERROR_INVALID_EMAIL);
            $oEvent->set('errorMessageTitle', gT("Failed to add user"));
            $oEvent->set('errorMessageBody', gT("The email address is not valid."));
            return null;
        }
        $new_pass = createPassword();
        // If user is being auto created we set parent ID to 1 (admin user)
        if (isset(Yii::app()->session['loginID'])) {
            $parentID = Yii::app()->session['loginID'];
        } else {
            $parentID = 1;
        }
        $iNewUID = User::model()->insertUser($new_user, $new_pass, $new_full_name, $parentID, $new_email);
        if (!$iNewUID) {
            $oEvent->set('errorCode', self::ERROR_ALREADY_EXISTING_USER);
            $oEvent->set('errorMessageTitle', '');
            $oEvent->set('errorMessageBody', gT("Failed to add user"));
            return null;
        }
        Permission::model()->setGlobalPermission($iNewUID, 'auth_ldap');

        $oEvent->set('newUserID', $iNewUID);
        $oEvent->set('newPassword', $new_pass);
        $oEvent->set('newEmail', $new_email);
        $oEvent->set('newFullName', $new_full_name);
        $oEvent->set('errorCode', self::ERROR_NONE);
        return $iNewUID;
    }

    /**
     * Create LDAP connection
     *
     * @return mixed
     */
    private function createConnection()
    {
        // Get configuration settings:
        $ldapserver     = $this->get('server');
        $ldapport       = $this->get('ldapport');
        $ldapver        = $this->get('ldapversion');
        $ldaptls        = $this->get('ldaptls');
        $ldapoptreferrals = $this->get('ldapoptreferrals');

        if (empty($ldapport)) {
            $ldapport = 389;
        }

        // Try to connect
        $ldapconn = ldap_connect($ldapserver, (int) $ldapport);
        if (false == $ldapconn) {
            return array("errorCode" => 1, "errorMessage" => gT('Error creating LDAP connection'));
        }

        // using LDAP version
        if ($ldapver === null) {
            // If the version hasn't been set, default = 2
            $ldapver = 2;
        }

        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, $ldapver);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, $ldapoptreferrals);

        if (!empty($ldaptls) && $ldaptls == '1' && $ldapver == 3 && preg_match("/^ldaps:\/\//", $ldapserver) == 0) {
            // starting TLS secure layer
            if (!ldap_start_tls($ldapconn)) {
                ldap_close($ldapconn); // all done? close connection
                return array("errorCode" => 100, 'errorMessage' => ldap_error($ldapconn));
            }
        }

        return $ldapconn;
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
        ->addContent(CHtml::tag('span', array(), "<label for='user'>".gT("Username")."</label>".CHtml::textField('user', '', array('size'=>40, 'maxlength'=>40, 'class'=>"form-control"))))
        ->addContent(CHtml::tag('span', array(), "<label for='password'>".gT("Password")."</label>".CHtml::passwordField('password', '', array('size'=>40, 'maxlength'=>40, 'class'=>"form-control"))));
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

            if ($ldapver == '2') {
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
                unset($aPluginSettings['mailattribute']);
                unset($aPluginSettings['fullnameattribute']);
                unset($aPluginSettings['autocreate']);
                unset($aPluginSettings['automaticsurveycreation']);
            }
        }

        return $aPluginSettings;
    }

    public function newUserSession()
    {
        // Do nothing if this user is not AuthLDAP type
        $identity = $this->getEvent()->get('identity');
        if ($identity->plugin != 'AuthLDAP') {
            return;
        }
        /* unsubscribe from beforeHasPermission, else updating event */
        $this->unsubscribe('beforeHasPermission');
        // Here we do the actual authentication
        $username = $this->getUsername();
        $password = $this->getPassword();

        $ldapmode = $this->get('ldapmode');
        $autoCreateFlag = false;
        $user = $this->api->getUserByName($username);
        // No user found!
        if ($user === null) {
            // If ldap mode is searchandbind and autocreation is enabled we can continue
            if ($ldapmode == 'searchandbind' && $this->get('autocreate', null, null, false) == true) {
                $autoCreateFlag = true;
            } else {
                // If the user doesnt exist in the LS database, he can not login
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID); // Error shown : user or password invalid
                return;
            }
        }
        if ($user !== null) {
            //If user cannot login via LDAP: setAuthFailure
            if (($user->uid == 1 && !$this->get('allowInitialUser'))
                || !Permission::model()->hasGlobalPermission('auth_ldap', 'read', $user->uid)
            ) {
                $this->setAuthFailure(self::ERROR_AUTH_METHOD_INVALID, gT('LDAP authentication method is not allowed for this user'));
                return;
            }
        }

        if (empty($password)) {
            // If password is null or blank reject login
            // This is necessary because in simple bind ldap server authenticates with blank password
            $this->setAuthFailure(self::ERROR_PASSWORD_INVALID); // Error shown : user or password invalid
            return;
        }

        // Get configuration settings:
        $suffix     		= $this->get('domainsuffix');
        $prefix     		= $this->get('userprefix');
        $searchuserattribute = $this->get('searchuserattribute');
        $extrauserfilter = $this->get('extrauserfilter');
        $usersearchbase = $this->get('usersearchbase');
        $binddn = $this->get('binddn');
        $bindpwd = $this->get('bindpwd');
        $groupsearchbase        = $this->get('groupsearchbase');
        $groupsearchfilter      = $this->get('groupsearchfilter');

        // Try to connect
        $ldapconn = $this->createConnection();
        if (!is_resource($ldapconn)) {
            $this->setAuthFailure($ldapconn['errorCode'], gT($ldapconn['errorMessage']));
            return;
        }

        if (empty($ldapmode) || $ldapmode == 'simplebind') {
            // in simple bind mode we know how to construct the userDN from the username
            $ldapbind = @ldap_bind($ldapconn, $prefix.$username.$suffix, $password);
        } else {
            // in search and bind mode we first do a LDAP search from the username given
            // to foind the userDN and then we procced to the bind operation
            if (empty($binddn)) {
                // There is no account defined to do the LDAP search,
                // let's use anonymous bind instead
                $ldapbindsearch = @ldap_bind($ldapconn);
            } else {
                // An account is defined to do the LDAP search, let's use it
                $ldapbindsearch = @ldap_bind($ldapconn, $binddn, $bindpwd);
            }
            if (!$ldapbindsearch) {
                $this->setAuthFailure(100, ldap_error($ldapconn));
                ldap_close($ldapconn); // all done? close connection
                return;
            }
            // Now prepare the search fitler
            if ($extrauserfilter != "") {
                $usersearchfilter = "(&($searchuserattribute=$username)$extrauserfilter)";
            } else {
                $usersearchfilter = "($searchuserattribute=$username)";
            }
            // Search for the user
            foreach (explode(";", $usersearchbase) as $usb) {
                $dnsearchres = ldap_search($ldapconn, $usb, $usersearchfilter, array($searchuserattribute));
                $rescount = ldap_count_entries($ldapconn, $dnsearchres);
                if ($rescount == 1) {
                    $userentry = ldap_get_entries($ldapconn, $dnsearchres);
                    $userdn = $userentry[0]["dn"];
                }
            }
            if(!$userentry) {
                // if no entry or more than one entry returned
                // then deny authentication
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
                ldap_close($ldapconn); // all done? close connection
                return;
            }

            // If specifed, check group membership
            if ($groupsearchbase != '' && $groupsearchfilter != '') {
                $keywords = array('$username', '$userdn');
                $substitutions = array($username, $userdn);
                $filter = str_replace($keywords, $substitutions, $groupsearchfilter);
                $groupsearchres = ldap_search($ldapconn, $groupsearchbase, $filter);
                $grouprescount = ldap_count_entries($ldapconn, $groupsearchres);
                if ($grouprescount < 1) {
                    $this->setAuthFailure(self::ERROR_USERNAME_INVALID,
                    gT('Valid username but not authorized by group restriction'));
                    ldap_close($ldapconn); // all done? close connection
                    return;
                }
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

        ldap_close($ldapconn); // all done? close connection

        // Finally, if user didn't exist and auto creation (i.e. autoCreateFlag == true) is enabled, we create it
        if ($autoCreateFlag) {
            if (($iNewUID = $this->_createNewUser($username)) && $this->get('automaticsurveycreation', null, null, false)) {
                Permission::model()->setGlobalPermission($iNewUID, 'surveys', array('create_p'));
            }
            $user = $this->api->getUserByName($username);
            if ($user === null) {
                $this->setAuthFailure(self::ERROR_USERNAME_INVALID, gT('Credentials are valid but we failed to create a user'));
                return;
            }
        }
        // If we made it here, authentication was a success and we do have a valid user
        $this->pluginManager->dispatchEvent(new PluginEvent('newUserLogin', $this));
        $this->setAuthSuccess($user);
    }
}
