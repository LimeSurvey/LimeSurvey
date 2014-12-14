<?php
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
     * Set username and password
     *
     * @return null
     */
    public function afterLoginFormSubmit()
    {
        // Here we handle post data
        $request = $this->api->getRequest();
        if ($request->getIsPostRequest()) {
            $this->setUsername( $request->getPost('user'));
            $this->setPassword($request->getPost('password'));
        }
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
        $identity = $this->getEvent()->set('identity', $identity);
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
        $event = $this->getEvent();
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