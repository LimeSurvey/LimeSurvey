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
*/
use ls\models\Question;
use ls\models\QuestionGroup;
use ls\models\Survey;

/**
* ls\models\Survey Common Action
*
* This controller contains common functions for survey related views.
*
* @package        LimeSurvey
* @subpackage    Backend
* @author        Shitiz Garg
*/
class Survey_Common_Action extends CAction
{
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        // Make sure viewHelper can be autoloaded
        Yii::import('application.helpers.viewHelper');
    }

    /**
    * Override runWithParams() implementation in CAction to help us parse
    * requests with subactions.
    *
    * @param array $params URL Parameters
    */
    public function runWithParams($params)
    {
        // Default method that would be called if the subaction and run() do not exist
        $sDefault = 'index';

        // Check for a subaction
        if (empty($params['sa']))
        {
            $sSubAction = $sDefault; // default
        }
        else
        {
            $sSubAction = $params['sa'];
        }
        // Check if the class has the method
        $oClass = new ReflectionClass($this);
        if (!$oClass->hasMethod($sSubAction))
        {
            // If it doesn't, revert to default Yii method, that is run() which should reroute us somewhere else
            $sSubAction = 'run';
        }

        // Populate the params. eg. surveyid -> iSurveyId
        $params = $this->_addPseudoParams($params);

        if (!empty($params['iSurveyId']))
        {
            if(!Survey::model()->findByPk($params['iSurveyId']))
            {
                $this->getController()->error('Invalid survey id');
            }
            elseif (!App ()->user->checkAccess('survey', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $params['iSurveyId']]))
            {
                $this->getController()->error('No permission');
            }
        }

        // Check if the method is public and of the action class, not its parents
        // ReflectionClass gets us the methods of the class and parent class
        // If the above method existence check passed, it might not be neceessary that it is of the action class
        $oMethod  = new ReflectionMethod($this, $sSubAction);

        // Get the action classes from the admin controller as the urls necessarily do not equal the class names. Eg. survey -> surveyaction
        $aActions = Yii::app()->getController()->getActionClasses();

        if(empty($aActions[$this->getId()]) || strtolower($oMethod->getDeclaringClass()->name) != strtolower($aActions[$this->getId()]) || !$oMethod->isPublic())
        {
            // Either action doesn't exist in our whitelist, or the method class doesn't equal the action class or the method isn't public
            // So let us get the last possible default method, ie. index
            $oMethod = new ReflectionMethod($this, $sDefault);
        }

        // We're all good to go, let's execute it
        // runWithParamsInternal would automatically get the parameters of the method and populate them as required with the params
        return parent::runWithParamsInternal($this, $oMethod, $params);
    }

    /**
    * Some functions have different parameters, which are just an alias of the
    * usual parameters we're getting in the url. This function just populates
    * those variables so that we don't end up in an error.
    *
    * This is also used while rendering wrapped template
    * {@link Survey_Common_Action::_renderWrappedTemplate()}
    *
    * @param array $params Parameters to parse and populate
    * @return array Populated parameters
    */
    private function _addPseudoParams($params)
    {
        // Return if params isn't an array
        if (empty($params) || !is_array($params))
        {
            return $params;
        }

        $pseudos = array(
        'id' => 'iId',
        'gid' => 'iGroupId',
        'qid' => 'iQuestionId',
        'sid' => array('iSurveyId', 'iSurveyID'),
        'surveyid' => array('iSurveyId', 'iSurveyID'),
        'srid' => 'iSurveyResponseId',
        'scid' => 'iSavedControlId',
        'uid' => 'iUserId',
        'ugid' => 'iUserGroupId',
        'fieldname' => 'sFieldName',
        'fieldtext' => 'sFieldText',
        'action' => 'sAction',
        'lang' => 'sLanguage',
        'browselang' => 'sBrowseLang',
        'tokenids' => 'aTokenIds',
        'tokenid' => 'iTokenId',
        'subaction' => 'sSubAction',
        );

        // Foreach pseudo, take the key, if it exists,
        // Populate the values (taken as an array) as keys in params
        // with that key's value in the params
        // (only if that place is empty)
        foreach ($pseudos as $key => $pseudo)
        {
            if (!empty($params[$key]))
            {
                $pseudo = (array) $pseudo;

                foreach ($pseudo as $pseud)
                {
                    if (empty($params[$pseud]))
                    {
                        $params[$pseud] = $params[$key];
                    }
                }
            }
        }

        // Fill param with according existing param, replace existing parameters.
        // iGroupId/gid can be found with qid/iQuestionId
        if(isset($params['iQuestionId']))
        {
            $oQuestion=Question::model()->find("qid=:qid",array(":qid"=>$params['iQuestionId']));//Move this in model to use cache
            if($oQuestion)
            {
                $params['iGroupId']=$params['gid']=$oQuestion->gid;
            }
        }
        // iSurveyId/iSurveyID/sid can be found with gid/iGroupId
        if(isset($params['iGroupId']))
        {
            $oGroup=QuestionGroup::model()->find("gid=:gid",array(":gid"=>$params['iGroupId']));//Move this in model to use cache
            if($oGroup)
            {
                $params['iSurveyId']=$params['iSurveyID']=$params['surveyid']=$params['sid']=$oGroup->sid;
            }
        }

        // Finally return the populated array
        return $params;
    }

    /**
    * Action classes require them to have a run method. We reroute it to index
    * if called.
    */
    public function run()
    {
        $this->index();
    }

    /**
    * Routes the action into correct subaction
    *
    * @access protected
    * @param string $sa
    * @param array $get_vars
    * @return void
    */
    protected function route($sa, array $get_vars)
    {
        $func_args = array();
        foreach ($get_vars as $k => $var)
            $func_args[$k] = Yii::app()->request->getQuery($var);

        return call_user_func_array(array($this, $sa), $func_args);
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * Addition of parameters should be avoided if they can be added to $aData
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array())
    {
        // Gather the data
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');

        $aData = $this->_addPseudoParams($aData);
        $aViewUrls = (array) $aViewUrls;
        $sViewPath = '/admin/';

        if (!empty($sAction))
        {
            $sViewPath .= $sAction . '/';
        }



        // Header
        ob_start();
        if(!isset($aData['display']['header']) || $aData['display']['header'] !== false)
        {
            // Send HTTP header
            header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
            Yii::app()->getController()->_getAdminHeader();
        }


        // Menu bars
        if (!isset($aData['display']['menu_bars']) || ($aData['display']['menu_bars'] !== false && (!is_array($aData['display']['menu_bars']) || !in_array('browse', array_keys($aData['display']['menu_bars'])))))
        {
            Yii::app()->getController()->_showadminmenu(!empty($aData['surveyid']) ? $aData['surveyid'] : null);

            if (!empty($aData['surveyid']))
            {

                LimeExpressionManager::StartProcessingPage(false, Yii::app()->baseUrl,true);  // so can click on syntax highlighting to edit questions


                if (isset($aData['display']['menu_bars']['surveysummary']))
                {

                    if ((empty($aData['display']['menu_bars']['surveysummary']) || !is_string($aData['display']['menu_bars']['surveysummary'])) && !empty($aData['gid']))
                    {
                        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
                    }
                    $this->_surveysummary($aData['surveyid'], !empty($aData['display']['menu_bars']['surveysummary']) ? $aData['display']['menu_bars']['surveysummary'] : null, !empty($aData['gid']) ? $aData['gid'] : null);
                }

                if (!empty($aData['gid']))
                {
                    if (empty($aData['display']['menu_bars']['gid_action']) && !empty($aData['qid']))
                    {
                        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
                    }

                    $this->_questiongroupbar($aData['surveyid'], $aData['gid'], !empty($aData['qid']) ? $aData['qid'] : null, !empty($aData['display']['menu_bars']['gid_action']) ? $aData['display']['menu_bars']['gid_action'] : null);

                    if (!empty($aData['qid']))
                    {
                        $this->_questionbar($aData['surveyid'], $aData['gid'], $aData['qid'], !empty($aData['display']['menu_bars']['qid_action']) ? $aData['display']['menu_bars']['qid_action'] : null);
                    }
                }


            }
        }



        // Load views
        foreach ($aViewUrls as $sViewKey => $viewUrl)
        {
            if (empty($sViewKey) || !in_array($sViewKey, array('message', 'output')))
            {
                if (is_numeric($sViewKey))
                {
                    Yii::app()->getController()->renderPartial($sViewPath . $viewUrl, $aData);
                }
                elseif (is_array($viewUrl))
                {
                    foreach ($viewUrl as $aSubData)
                    {
                        $aSubData = array_merge($aData, $aSubData);
                        Yii::app()->getController()->renderPartial($sViewPath . $sViewKey, $aSubData);
                    }
                }
            }
            else
            {
                switch ($sViewKey)
                {
                    // Message
                    case 'message' :
                        if (empty($viewUrl['class']))
                        {
                            Yii::app()->getController()->_showMessageBox($viewUrl['title'], $viewUrl['message']);
                        }
                        else
                        {
                            Yii::app()->getController()->_showMessageBox($viewUrl['title'], $viewUrl['message'], $viewUrl['class']);
                        }
                        break;

                        // Output
                    case 'output' :
                        echo $viewUrl;
                        break;
                }
            }
        }

        // Footer
        if(!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false)
            Yii::app()->getController()->_loadEndScripts();

        if(!isset($aData['display']['footer']) || $aData['display']['footer'] !== false)
            Yii::app()->getController()->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));

        $out = ob_get_contents();
        ob_clean();
        App()->getClientScript()->render($out);
        echo $out;
    }


    protected function _filterImportedResources($extractdir, $destdir)
    {
        $aErrorFilesInfo = array();
        $aImportedFilesInfo = array();

        if (!is_dir($extractdir))
            return array(array(), array());

        if (!is_dir($destdir))
            mkdir($destdir);

        $dh = opendir($extractdir);

        while ($direntry = readdir($dh))
        {
            if ($direntry != "." && $direntry != "..")
            {
                if (is_file($extractdir . "/" . $direntry))
                {
                    // is  a file
                    $extfile = substr(strrchr($direntry, '.'), 1);
                    if (!(stripos(',' . Yii::app()->getConfig('allowedresourcesuploads') . ',', ',' . $extfile . ',') === false))
                    {
                        // Extension allowed
                        if (!copy($extractdir . "/" . $direntry, $destdir . "/" . $direntry))
                        {
                            $aErrorFilesInfo[] = Array(
                            "filename" => $direntry,
                            "status" => gT("Copy failed")
                            );
                        }
                        else
                        {
                            $aImportedFilesInfo[] = Array(
                            "filename" => $direntry,
                            "status" => gT("OK")
                            );
                        }
                    }
                    else
                    {
                        // Extension forbidden
                        $aErrorFilesInfo[] = Array(
                        "filename" => $direntry,
                        "status" => gT("Forbidden Extension")
                        );
                    }
                    unlink($extractdir . "/" . $direntry);
                }
            }
        }

        return array($aImportedFilesInfo, $aErrorFilesInfo);
    }

    /**
    * Creates a temporary directory
    *
    * @access protected
    * @param string $dir
    * @param string $prefix
    * @param int $mode
    * @return string
    */
    protected function _tempdir($dir, $prefix='', $mode=0700)
    {
        if (substr($dir, -1) != DIRECTORY_SEPARATOR)
            $dir .= DIRECTORY_SEPARATOR;

        do
        {
            $path = $dir . $prefix . mt_rand(0, 9999999);
        }
        while (!mkdir($path, $mode));

        return $path;
    }

}
