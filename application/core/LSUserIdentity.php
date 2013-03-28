<?php
/*
* LimeSurvey
* Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/
class LSUserIdentity extends CUserIdentity {

    const ERROR_IP_LOCKED_OUT = 98;
    const ERROR_UNKNOWN_HANDLER = 99;
    
    /**
     * The userid
     *  
     * @var int
     */
    protected $id = null;

    /**
     * A User::model() object
     * 
     * @var User
     */
    protected $user;
    
    /**
     * This is the name of the plugin to handle authentication
     * default handler is used for remote control
     * 
     * @var string
     */
    public $plugin = 'Authdb';

    public function authenticate() {
        // First initialize the result, we can later retieve it to get the exact error code/message
        $this->_result = new LSAuthResult();
        
        // Check if the ip is locked out
        if (Failed_login_attempts::model()->isLockedOut()) {
            $this->_result->setError(self::ERROR_IP_LOCKED_OUT);
        }
        
        // If still ok, continue
        if ($this->_result->isValid())
        { 
            if (is_null($this->plugin)) {
                $this->_result->setError(self::ERROR_UNKNOWN_HANDLER);
            } else {
                // Delegate actual authentication to plugin
                $authEvent = new PluginEvent('newSession', $this);
                App()->getPluginManager()->dispatchEvent($authEvent, array($this->plugin));
                $result = $authEvent->get('result');
                if ($result instanceof LSAuthResult) {
                
                }
            }
        }
         
        @session_regenerate_id(); // Prevent session fixation
        if ($this->_result->isValid()) {
            // Perform postlogin
            $this->postLogin();
            
        } else {
            // Log a failed attempt
            $userHostAddress = App()->request->getUserHostAddress();
            Failed_login_attempts::model()->addAttempt($userHostAddress);
        }
        
        return $this->_result->isValid();        
    }
    
    public function setPlugin($name) {
        $this->plugin = $name;
    }
}