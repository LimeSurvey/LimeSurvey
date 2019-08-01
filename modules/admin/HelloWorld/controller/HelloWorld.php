<?php
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
* HelloWorld admin module
* This admin module is just here to show you how to add an admin module in LimeSurvey
*/

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/* Note: Class name must identical to folder name */
class HelloWorld extends Survey_Common_Action
{
      /**
       * Default action
       * @return  array Populated parameters ready to be rendered inside the admin interface
       */
      public function index()
      {
        return $this->sayHello();
      }

    /**
     * Says hello to user
     * @param string $sWho who to say hello
     * @return  array Populated parameters ready to be rendered inside the admin interface
     */
    public function sayHello($sWho="World")
    {
        // Call to Survey_Common_Action::_renderWrappedTemplate that will generate the "Layout"
        $this->_renderWrappedTemplate('HelloWorld', 'index', array(
            'sWho'=>$sWho,
        ));
    }

    /**
     * Override Survey_Common_Action::renderCentralContents
     * If you don't understand what it does, just copy / paste it in your own admin module
     * We let it here just in case you're trying to do something different
     */
    protected function renderCentralContents($sAction, $aViewUrls, $aData = [])
    {
      // Use alias to render a view outisde of application directory.
      return Yii::app()->getController()->renderPartial('lsadminmodules.' . $sAction. '.views.' . $aViewUrls, $aData, true);
    }

}
