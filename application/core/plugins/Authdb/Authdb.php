<?php
class Authdb extends AuthPluginBase
{
    protected $storage = 'DbStorage';
    
    protected $_onepass = null;
    
    static protected $description = 'Core: Database authentication';
    
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
        $this->getEvent()->set('default', get_class($this));   // This is the default login method, should be configurable from plugin settings
        
        // We can skip the login form here and set username/password etc.
        
        $request = $this->api->getRequest();
        if ($request->getIsPostRequest() && !is_null($request->getQuery('onepass'))) {
            // We have a one time password, skip the login form
            $this->setOnePass($request()->getQuery('onepass'));
            $this->setUsername($request()->getQuery('user'));
            $this->setAuthPlugin(); // This plugin will handle authentication ans skips the login form
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
        $onepass  = $this->getOnePass();
        
        $user = $this->api->getUserByName($username);
        
        if ($user !== null)
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
}