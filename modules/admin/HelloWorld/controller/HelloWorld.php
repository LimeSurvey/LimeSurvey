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
       *
       * This method is for a full page module. You can reach it via: index.php?r=admin/HelloWorld/
       *
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
       * Example of two functions to show inside the survey layout
       *
       */


      /*
       * This function is the root one. It will say "hello root" and nothing else. You can access it via :
       * index.php?r=admin/HelloWorld/sa/HelloWorldSurvey&surveyid=XXXXX
       * where XXXXX is any valid survey id.
       */
      public function HelloWorldSurvey($surveyid)
      {
        // First, we get the survey model to get some information
        $oSurvey = Survey::model()->findByPk($surveyid);

        // Then, we build the data array that will be passed to the view.
        $aData = array();

        // Those datas will controll the behaviour of the survey "layout"

        // By providing a surveyid, we launch the survey "layout".
        // see: https://github.com/LimeSurvey/LimeSurvey/blob/ae760dd3274a390b790c494f50826cb3a56f37c3/application/core/Survey_Common_Action.php#L328-L338
        $aData['surveyid'] = $surveyid;

        // By providing a "title_bar", we the green top bar with the breadcrumb.
        // see: https://github.com/LimeSurvey/LimeSurvey/blob/ae760dd3274a390b790c494f50826cb3a56f37c3/application/core/Survey_Common_Action.php#L481-L486
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$surveyid.")";

        // By providing a module subaction, we launch the breadcrumb
        $aData['title_bar']['module_subaction'] = "HelloWorld";
        $aData['title_bar']['module_subaction_url'] = App()->createUrl('admin/HelloWorld/sa/HelloWorldSurvey/', ['surveyid' => $oSurvey->sid]);

        // Our own datas for our view
        $aData['sWho'] = "root";

        // Call to Survey_Common_Action::_renderWrappedTemplate that will generate the "Layout"
        $this->_renderWrappedTemplate('HelloWorld', 'HelloWorldSurvey', $aData, false);
      }


    /**
     * Says hello to user
     * @param string $sWho just to show you can pass parameters from url
     * @return  array Populated parameters ready to be rendered inside the admin interface
     */
    public function sayHelloUser($sWho="World", $surveyid)
    {
        // First, we get the survey model to get some information
        $oSurvey = Survey::model()->findByPk($surveyid);


        // Then, we build the data array that will be passed to the view.
        $aData = array();

        // Those datas will controll the behaviour of the survey "layout"

        // By providing a surveyid, we launch the survey "layout".
        // see: https://github.com/LimeSurvey/LimeSurvey/blob/ae760dd3274a390b790c494f50826cb3a56f37c3/application/core/Survey_Common_Action.php#L328-L338
        $aData['surveyid'] = $surveyid;

        // By providing a "title_bar", we the green top bar with the breadcrumb.
        // see: https://github.com/LimeSurvey/LimeSurvey/blob/ae760dd3274a390b790c494f50826cb3a56f37c3/application/core/Survey_Common_Action.php#L481-L486
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$surveyid.")";

        // By providing a module subaction, we launch the breadcrumb
        $aData['title_bar']['module_subaction'] = "HelloWorld";
        $aData['title_bar']['module_subaction_url'] = App()->createUrl('admin/HelloWorld/sa/HelloWorldSurvey/', [ 'surveyid' => $oSurvey->sid]);
        $aData['title_bar']['module_current_action'] = 'sayHelloUser';

        // Our own datas for our view
        $aData['sWho'] = $sWho;
        $aData['sUserName'] = Yii::app()->user->name;

        // Call to Survey_Common_Action::_renderWrappedTemplate that will generate the "Layout"
        $this->_renderWrappedTemplate('HelloWorld', 'sayHelloUser', $aData, false);
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
