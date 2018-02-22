<?php
namespace LimeSurvey\PluginManager;
use \User;
use LSAuthResult;

abstract class AuthPluginBase extends PluginBase
{
    
    /**
     * These constants reflect the error codes to be used by the identity, they 
     * are copied from LSUserIdentity and CBaseUserIdentity for easier access.
     */
    const ERROR_NONE = 0;
    const ERROR_NOT_ADDED = 5;
    const ERROR_USERNAME_INVALID = 10;
    const ERROR_PASSWORD_INVALID = 20;
    const ERROR_AUTH_METHOD_INVALID = 30;
    const ERROR_IP_LOCKED_OUT = 98;
    const ERROR_UNKNOWN_HANDLER = 99;
    const ERROR_UNKNOWN_IDENTITY = 100;
    const ERROR_INVALID_EMAIL = 110;
    const ERROR_ALREADY_EXISTING_USER = 120;
    const ERROR_LDAP_CONNECTION = 130;
    const ERROR_LDAP_MODE = 135;
    const ERROR_LDAP_NO_EMAIL = 140;
    const ERROR_LDAP_NO_FULLNAME = 150;
    const ERROR_LDAP_NO_BIND = 160;
    const ERROR_LDAP_NO_SEARCH_RESULT = 170;

    const LDAP_INVALID_PASSWORD_TEXT = "INVALID_PASSWORD-LDAP_USER";

    protected $_username = null;
    protected $_password = null;
    
    /**
     * Get the password (if set)
     * 
     * @return string|null
     */
    protected function getPassword()
    {
        return $this->_password;
    }
        
    /**
     * Get the username (if set)
     * 
     * @return string|null
     */
    protected function getUserName()
    {
        return $this->_username;
    }

    /**
     * Set username and password by post request
     *
     * @return null
     */
    public function afterLoginFormSubmit()
    {
        // Here we handle post data
        $request = $this->api->getRequest();
        if ($request->getIsPostRequest()) {
            $this->setUsername($request->getPost('user'));
            $this->setPassword($request->getPost('password'));
        }
    }

    /**
     * Set username and password by event
     *
     * @return null
     */
    public function remoteControlLogin()
    {
        $event = $this->getEvent();
        $this->setUsername($event->get('username'));
        $this->setPassword($event->get('password'));
    }

    /**
     * Set authentication result to success for the given user object.
     * 
     * @param User $user
     * @return AuthPluginBase
     */
    public function setAuthSuccess(User $user)
    {
        $event = $this->getEvent();
        $identity = $this->getEvent()->get('identity');
        $identity->id = $user->uid;
        $identity->user = $user;
        $this->getEvent()->set('identity', $identity);
        $event->set('result', new LSAuthResult(self::ERROR_NONE));
        
        return $this;
    }
    
    /**
     * Set authentication result to failure.
     * 
     * @param int $code Any of the constants defined in this class
     * @param string $message An optional message to return about the failure
     * @return AuthPluginBase
     */
    public function setAuthFailure($code = self::ERROR_UNKNOWN_IDENTITY, $message = '')
    {
        $event = $this->getEvent();
        $identity = $this->getEvent()->get('identity');
        $identity->id = null;
        $event->set('result', new LSAuthResult($code, $message));
        
        return $this;
    }
    
    /**
     * Set this plugin to handle the authentication
     * 
     * @return AuthPluginBase
     */
    public function setAuthPlugin()
    {
        $this->getEvent();
        $identity = $this->getEvent()->get('identity');
        $identity->plugin = get_class($this);
        $this->getEvent()->stop();
        
        return $this;
    }
    
    /**
     * Set the password to use for authentication
     * 
     * @param string $password
     * @return AuthPluginBase
     */
    protected function setPassword($password)
    {
        $this->_password = $password;
        $event = $this->getEvent();
        $identity = $this->getEvent()->get('identity');
        $identity->password = $password;
        
        $event->set('identity', $identity);
        
        return $this;
    }
    
    /**
     * Set the username to use for authentication
     * 
     * @param string $username The username
     * @return AuthPluginBase
     */
    protected function setUsername($username)
    {
        $this->_username = $username;
        $event = $this->getEvent();
        $identity = $this->getEvent()->get('identity');
        $identity->username = $username;
        
        $event->set('identity', $identity);
        
        return $this;
    }
}
