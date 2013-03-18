<?php 
    Yii::import('application.helpers.Hash', true);

    class LSWebUser extends CWebUser
    {
        protected $sessionVariable = 'LSWebUser';
        
        
        public function __construct() 
        {
            if (!isset($_SESSION[$this->sessionVariable]))
            {
                $_SESSION[$this->sessionVariable] = array();
            }
        }
        
        public function getStateKeyPrefix() 
        {
            return $this->sessionVariable;
        }
        
        public function getState($key, $defaultValue = null) 
        {
            if (!Hash::check($_SESSION[$this->sessionVariable], $key))
            {
                return $defaultValue;
            }
            else
            {
                return Hash::get($_SESSION[$this->sessionVariable], $key);
            }
        }
        
        public function setState($key, $value, $defaultValue = null) 
        {
            if($value === $defaultValue)
            {
                $_SESSION[$this->sessionVariable] = Hash::remove($_SESSION[$this->sessionVariable], $key);
            }
            else
            {
                $_SESSION[$this->sessionVariable] = Hash::insert($_SESSION[$this->sessionVariable], $key, $value);
            }
                
            
        }
        
        
    }
?>