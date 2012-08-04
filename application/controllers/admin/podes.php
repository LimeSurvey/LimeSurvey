<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
* podes
*
* @package IncubatorSurvey
* @author  Rahmat Awaludin (rahmat.awaludin@gmail.com)
* @copyright 2012
* @version $Id: podes.php 
* @access public
*/
class Podes extends Survey_Common_Action
{
    /**
    * Initiates the survey action, checks for superadmin permission
    *
    * @access public
    * @param CController $controller
    * @param string $id
    * @return void
    */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);
    }
    
    /**
    * This function show form to select location for podes
    *
    */
    function index()
    {
        /* if (!hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
            $this->getController()->error('No permission'); */          
      
        $aData['nilai'] = "nilai pertama";     
        //$aData = array_merge($aData, $this->_tabTokens($esrow));
        
        $this->_renderWrappedTemplate('podes', 'index_view', $aData);
    }
    
    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'podes', $aViewUrls = array(), $aData = array())
    {
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}