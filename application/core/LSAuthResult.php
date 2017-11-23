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
class LSAuthResult
{
    protected $_code;
    protected $_message;
    
    public function __construct($code = 0, $message = '')
    {
        $this->setError($code, $message);
    }
    
    public function isValid()
    {
        if ($this->_code === 0) {
            return true;
        }
        
        return false;
    }
    
    public function getCode()
    {
        return $this->_code;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function setError($code, $message = null)
    {
        $this->_code = $code;
        $this->_message = $message;
    }
}