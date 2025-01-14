<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The GititSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* GititSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* GlobalSettings custom admin module
* This admin module extend the core GlobalSettings controller
*/


// First we define a namespace to avoid collision with core class.
// Since we do that, all our call to the core/framework classes will need the global name space: /
// For example:  \Yii::app()->getController()->renderPartial...
namespace lsadminmodules\globalsettings\controller;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/* Note: Class name must identical to folder name and to the core class you want to override*/
class GlobalSettings extends \GlobalSettings
{

    public $myNewParam = "This was not in global setting core controller"; // Just an example to show how to override a parent method

    /**
     * A brand new helloworld function for GlobalSettings !
     *
     * You can reach it via: index.php?r=admin/globalsettings/sa/HelloWorld/
     *
     * @param string $sWho who to say hello
     * @return  array Populated parameters ready to be rendered inside the admin interface
     */
    public function HelloWorld($sWho="World")
    {
        // Call to Survey_Common_Action::_renderWrappedTemplate that will generate the "Layout"
        $this->_renderWrappedTemplate('globalsettings', 'HelloWorld', array(
            'sWho'=>$sWho,
          ));

    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        // We add ou new paramater to the data to parse to the view
        $aData["myNewParam"] = $this->myNewParam;

        // Then we just call the parent method
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * Override Survey_Common_Action::renderCentralContents
     *
     * If you don't understand what it does, just copy / paste it in your own admin module
     * We let it here just in case you're trying to do something different
     *
     * NOTE: you just need to copy/paste here any view called by the core GlobalSettings to override it.
     *
     */
    protected function renderCentralContents($sAction, $aViewUrls, $aData = [])
    {
      if ( file_exists ( \Yii::getPathOfAlias('lsadminmodules.'.$sAction.'.views.' . $aViewUrls) . '.php' )  ){
        // Use alias to render a view outisde of application directory.
        return \Yii::app()->getController()->renderPartial('lsadminmodules.'.$sAction.'.views.' . $aViewUrls, $aData, true);
      }else{
        //  var_dump( \Yii::getPathOfAlias('lsadminmodules.' . $sAction. '.views.' . $aViewUrls) );  die();
        return parent::renderCentralContents($sAction, $aViewUrls, $aData );
      }

    }
}
