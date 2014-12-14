<?php
class Authdb extends AuthPluginBase
{
    protected $storage = 'DbStorage';
    protected $_onepass = null;

    static protected $description = 'Core: Database authentication + exports';
    static protected $name = 'LimeSurvey internal database';

    public function __construct(PluginManager $manager, $id)
    {
        parent::__construct($manager, $id);

        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeLogin');
        $this->subscribe('newLoginForm');
        $this->subscribe('afterLoginFormSubmit');
        $this->subscribe('newUserSession');
        $this->subscribe('beforeDeactivate');

        // Now register for the core exports
        $this->subscribe('listExportPlugins');
        $this->subscribe('listExportOptions');
        $this->subscribe('newExport');
        
    }

    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);

        // Optionally set a custom error message.
        $this->getEvent()->set('message', gT('Core plugin can not be disabled.'));
    }

    public function beforeLogin()
    {
        $this->getEvent()->set('default', get_class($this));   // This is the default login method, should be configurable from plugin settings
        
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
        $sUserName='';
        $sPassword='';
        if (Yii::app()->getConfig("demoMode") === true && Yii::app()->getConfig("demoModePrefill") === true)
        {
            $sUserName=Yii::app()->getConfig("defaultuser");
            $sPassword=Yii::app()->getConfig("defaultpass");
        }

        $this->getEvent()->getContent($this)
             ->addContent(CHtml::tag('li', array(), "<label for='user'>"  . gT("Username") . "</label>".CHtml::textField('user',$sUserName,array('size'=>40,'maxlength'=>40))))
             ->addContent(CHtml::tag('li', array(), "<label for='password'>"  . gT("Password") . "</label>".CHtml::passwordField('password',$sPassword,array('size'=>40,'maxlength'=>40))));
    }

    public function newUserSession()
    {
        // Do nothing if this user is not Authdb type
        $identity = $this->getEvent()->get('identity');
        if ($identity->plugin != 'Authdb')
        {
            return;
        }

        // Here we do the actual authentication       
        $username = $this->getUsername();
        $password = $this->getPassword();
        $onepass  = $this->getOnePass();

        $user = $this->api->getUserByName($username);

        if ($user !== null and $username==$user->users_name) // Control of equality for uppercase/lowercase with mysql
        {
            if (gettype($user->password)=='resource')
            {
                $sStoredPassword=stream_get_contents($user->password,-1,0);  // Postgres delivers bytea fields as streams :-o
            }
            else
            {
                $sStoredPassword=$user->password;
            }
        }
        else
        {
            $this->setAuthFailure(self::ERROR_USERNAME_INVALID);
            return;
        }

        if ($onepass != '' && $this->api->getConfigKey('use_one_time_passwords') && md5($onepass) == $user->one_time_pw)
        {
            $user->one_time_pw='';
            $user->save();
            $this->setAuthSuccess($user);
            return;
        }
        
        if ($sStoredPassword !== hash('sha256', $password))
        {
            $this->setAuthFailure(self::ERROR_PASSWORD_INVALID);
            return;
        }
        $this->setAuthSuccess($user);
    }

    /**
     * Set the onetime password
     * 
     * @param type $onepass
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
                    $label .= '<font class="warningtitle">'.$clang->gT("(Iconv Library not installed)").'</font>';
                }
                $event->set('label', $label);
                break;
                
            case 'doc':
                $event->set('label', gT("Microsoft Word"));
                $event->set('onclick', 'document.getElementById("ansfull").checked=true;document.getElementById("ansabbrev").disabled=true;');
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
