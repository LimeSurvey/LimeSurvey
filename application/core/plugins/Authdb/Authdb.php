<?php
class Authdb extends AuthPluginBase
{
    protected $storage = 'DbStorage';
    protected $_onepass = null;

    static protected $description = 'Core: Database authentication + exports';
    static protected $name = 'LimeSurvey internal database';

    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('createNewUser');
        $this->subscribe('beforeLogin');
        $this->subscribe('newLoginForm');
        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('remoteControlLogin');

        $this->subscribe('newUserSession');
        $this->subscribe('beforeDeactivate');
        // Now register for the core exports
        $this->subscribe('listExportPlugins');
        $this->subscribe('listExportOptions');
        $this->subscribe('newExport');
    }

    /**
     * Create a DB user
     *
     * @return void
     */
    public function createNewUser()
    {
        if (!Permission::model()->hasGlobalPermission('users', 'create')) {
            return;
        }

        // Do nothing if the user to be added is not DB type
        if (flattenText(Yii::app()->request->getPost('user_type')) != 'DB') {
            return;
        }
        $oEvent = $this->getEvent();
        $new_user = flattenText(Yii::app()->request->getPost('new_user'), false, true);
        $new_email = flattenText(Yii::app()->request->getPost('new_email'), false, true);
        if (!validateEmailAddress($new_email)) {
            $oEvent->set('errorCode', self::ERROR_INVALID_EMAIL);
            $oEvent->set('errorMessageTitle', gT("Failed to add user"));
            $oEvent->set('errorMessageBody', gT("The email address is not valid."));
            return;
        }
        $new_full_name = flattenText(Yii::app()->request->getPost('new_full_name'), false, true);
        $new_pass = createPassword();
        $iNewUID = User::model()->insertUser($new_user, $new_pass, $new_full_name, Yii::app()->session['loginID'], $new_email);
        if (!$iNewUID) {
            $oEvent->set('errorCode', self::ERROR_ALREADY_EXISTING_USER);
            $oEvent->set('errorMessageTitle', '');
            $oEvent->set('errorMessageBody', gT("Failed to add user"));
            return;
        }

        Permission::model()->setGlobalPermission($iNewUID, 'auth_db');

        $oEvent->set('newUserID', $iNewUID);
        $oEvent->set('newPassword', $new_pass);
        $oEvent->set('newEmail', $new_email);
        $oEvent->set('newFullName', $new_full_name);
        $oEvent->set('errorCode', self::ERROR_NONE);
    }

    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);

        // Optionally set a custom error message.
        $this->getEvent()->set('message', gT('Core plugin can not be disabled.'));
    }

    public function beforeLogin()
    {
        // We can skip the login form here and set username/password etc.
        $request = $this->api->getRequest();
        if (!is_null($request->getParam('onepass'))) {
            // We have a one time password, skip the login form
            $this->setOnePass($request->getParam('onepass'));
            $this->setUsername($request->getParam('user'));
            $this->setAuthPlugin(); // This plugin will handle authentication and skips the login form
        }
    }

    /**
     * Get the onetime password (if set)
     *
     * @return string|null
     */
    protected function getOnePass()
    {
        return $this->_onepass;
    }

    public function newLoginForm()
    {
        $sUserName = '';
        $sPassword = '';
        if (Yii::app()->getConfig("demoMode") === true && Yii::app()->getConfig("demoModePrefill") === true) {
            $sUserName = Yii::app()->getConfig("defaultuser");
            $sPassword = Yii::app()->getConfig("defaultpass");
        }

        $this->getEvent()->getContent($this)
                ->addContent(CHtml::tag('span', array(), "<label for='user'>".gT("Username")."</label>".CHtml::textField('user', $sUserName, array('size'=>72, 'maxlength'=>72, 'class'=>"form-control"))))
                ->addContent(CHtml::tag('span', array(), "<label for='password'>".gT("Password")."</label>".CHtml::passwordField('password', $sPassword, array('size'=>72, 'maxlength'=>72, 'class'=>"form-control"))));
    }

    public function newUserSession()
    {
        // Do nothing if this user is not Authdb type
        $identity = $this->getEvent()->get('identity');

        if ($identity->plugin != 'Authdb') {
            return;
        }

        // Here we do the actual authentication
        $username = $this->getUsername();
        $password = $this->getPassword();
        $onepass  = $this->getOnePass();

        $user = $this->api->getUserByName($username);

        if ($user === null) {
            $user = $this->api->getUserByEmail($username);
            if (is_object($user)) {
                $this->setUsername($user->users_name);
            }
        }
        if ($user !== null && $user->uid != 1 && !Permission::model()->hasGlobalPermission('auth_db', 'read', $user->uid)) {
            $this->setAuthFailure(self::ERROR_AUTH_METHOD_INVALID, gT('Internal database authentication method is not allowed for this user'));
            return;
        }
        if ($user === null) {
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            return;
        }
        if ($user !== null && ($username != $user->users_name && $username != $user->email)) {
// Control of equality for uppercase/lowercase with mysql
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            return;
        }


        if ($onepass != '' && $this->api->getConfigKey('use_one_time_passwords') && hash('sha256',$onepass) == $user->one_time_pw) {
            $user->one_time_pw = '';
            $user->save();
            $this->setAuthSuccess($user);
            return;
        }

        if (!$user->checkPassword($password)) {
            $this->setAuthFailure(self::ERROR_PASSWORD_INVALID);
            return;
        }
        $this->setAuthSuccess($user);
    }

    /**
     * Set the onetime password
     *
     * @param string $onepass
     * @return Authdb
     */
    protected function setOnePass($onepass)
    {
        $this->_onepass = $onepass;

        return $this;
    }


    // Now the export part:
    public function listExportOptions()
    {
        $event = $this->getEvent();
        $type = $event->get('type');

        switch ($type) {
            case 'csv':
                $event->set('label', gT("CSV"));
                $event->set('default', true);
                break;
            case 'xls':
                $label = gT("Microsoft Excel");
                if (!function_exists('iconv')) {
                    $label .= '<font class="warningtitle">'.gT("(Iconv Library not installed)").'</font>';
                }
                $event->set('label', $label);
                break;
            case 'doc':
                $event->set('label', gT("Microsoft Word"));
                $event->set('onclick', 'document.getElementById("answers-long").checked=true;document.getElementById("answers-short").disabled=true;');
                break;
            case 'pdf':
                $event->set('label', gT("PDF"));
                break;
            case 'html':
                $event->set('label', gT("HTML"));
                break;
            case 'json':    // Not in the interface, only for RPC
            default:
                break;
        }
    }

    /**
     * Registers this export type
     */
    public function listExportPlugins()
    {
        $event = $this->getEvent();
        $exports = $event->get('exportplugins');

        // Yes we overwrite existing classes if available
        $className = get_class();
        $exports['csv'] = $className;
        $exports['xls'] = $className;
        $exports['pdf'] = $className;
        $exports['html'] = $className;
        $exports['json'] = $className;
        $exports['doc'] = $className;

        $event->set('exportplugins', $exports);
    }

    /**
     * Returns the required IWriter
     */
    public function newExport()
    {
        $event = $this->getEvent();
        $type = $event->get('type');

        switch ($type) {
            case "doc":
                $writer = new DocWriter();
                break;
            case "xls":
                $writer = new ExcelWriter();
                break;
            case "pdf":
                $writer = new PdfWriter();
                break;
            case "html":
                $writer = new HtmlWriter();
                break;
            case "json":
                $writer = new JsonWriter();
                break;
            case "csv":
            default:
                $writer = new CsvWriter();
                break;
        }

        $event->set('writer', $writer);
    }
}
