<?php
namespace ls\pluginmanager;
use \User;
use LSAuthResult;

abstract class AuthPluginBase extends PluginBase {
    
    /**
     * These constants reflect the error codes to be used by the identity, they 
     * are copied from LSUserIdentity and CBaseUserIdentity for easier access.
     */
    const ERROR_NONE = 0;
	const ERROR_USERNAME_INVALID = 1;
	const ERROR_PASSWORD_INVALID = 2;
    const ERROR_IP_LOCKED_OUT = 98;
    const ERROR_UNKNOWN_HANDLER = 99;
    const ERROR_UNKNOWN_IDENTITY = 100;
    
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
     * Set username and password
     *
     * @return null
     */
    public function eventAfterLoginFormSubmit($event)
    {
        // Here we handle post data
        $request = $this->api->getRequest();
        if ($request->getIsPostRequest()) {
            $this->setUsername($event, $request->getPost('user'));
            $this->setPassword($event, $request->getPost('password'));
        }
    }

    /**
     * Set authentication result to success for the given user object.
     * 
     * @param User $user
     * @return AuthPluginBase
     */
    public function setAuthSuccess(PluginEvent $event, User $user)
    {
        $identity = $event->get('identity');
        $identity->id = $user->uid;
        $identity->user = $user;
        $event->set('result', new LSAuthResult(self::ERROR_NONE));
        
        return $this;
    }
    
    abstract public function authenticate(\CHttpRequest $request);
    
    abstract public function getLoginSettings();
    
}