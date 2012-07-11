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

class remotecontrol extends Survey_Common_Action
{
    /**
    * @var Zend_XmlRpc_Server
    */
    protected $xmlrpc;

    /**
    * This is the XML-RPC server routine
    *
    * @access public
    * @return void
    */
    public function run()
    {
        $RPCType=Yii::app()->getConfig("RPCInterface");

        if ($RPCType=='xml')
        {
            $cur_path = get_include_path();

            set_include_path($cur_path . PATH_SEPARATOR . APPPATH . 'helpers');

            // Yii::import was causing problems for some odd reason
            require_once('Zend/XmlRpc/Server.php');
            require_once('Zend/XmlRpc/Server/Exception.php');
            require_once('Zend/XmlRpc/Value/Exception.php');
            $this->xmlrpc = new Zend_XmlRpc_Server();
            $this->xmlrpc->sendArgumentsToAllMethods(false);
            $this->xmlrpc->setClass('remotecontrol_handle', '', $this->controller);
            echo $this->xmlrpc->handle();
        }
        elseif($RPCType=='json')
        {
            Yii::app()->loadLibrary('jsonRPCServer');
            $oHandler=new remotecontrol_handle($this->controller);
            jsonRPCServer::handle($oHandler);
        }
        exit;
    }

    /**
    * Simple procedure to test most RPC functions
    *
    */
    public function test()
    {
        Yii::app()->loadLibrary('jsonRPCClient');
        $myJSONRPCClient = new jsonRPCClient(Yii::app()->getBaseUrl(true).'/'.dirname(Yii::app()->request->getPathInfo()));
        $sSessionKey= $myJSONRPCClient->get_session_key('admin','password');
        if (is_array($sSessionKey)) {echo $sSessionKey['status']; die();}
        else
        {
            echo 'Retrieved session key'.'<br>';
        }

        $sLSSData=file_get_contents(dirname(Yii::app()->basePath).DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'demosurveys'.DIRECTORY_SEPARATOR.'limesurvey2_sample_survey_english.lss');
        $iSurveyID=$myJSONRPCClient->import_survey($sSessionKey, $sLSSData);
        echo 'Created new survey SID:'.$iSurveyID.'<br>';

        $aResult=$myJSONRPCClient->activate_survey($sSessionKey, $iSurveyID);
        if ($aResult['status']=='OK')
        {
            echo 'Survey '.$iSurveyID.' successfully activated.<br>';
        }
        $aResult=$myJSONRPCClient->activate_tokens($sSessionKey, $iSurveyID,array(1,2));
        if ($aResult['status']=='OK')
        {
            echo 'Tokens for Survey ID '.$iSurveyID.' successfully activated.<br>';
        }
            die();
        $aResult=$myJSONRPCClient->delete_survey($sSessionKey, $iSurveyID);
        echo 'Deleted survey SID:'.$iSurveyID.'-'.$aResult['status'].'<br>';

        // Release the session key - close the session
        $Result= $myJSONRPCClient->release_session_key($sSessionKey);
        echo 'Closed the session'.'<br>';

    }


}
/**
* This class handles all methods of the RPCs
*/
class remotecontrol_handle
{
    /**
    * @var AdminController
    */
    protected $controller;

    /**
    * Constructor, stores the action instance into this handle class
    *
    * @access public
    * @param AdminController $controller
    * @return void
    */
    public function __construct(AdminController $controller)
    {
        $this->controller = $controller;
    }


    /**
    * RPC routine to create a session key
    *
    * @access public
    * @param string $username
    * @param string $password
    * @return string
    */
    public function get_session_key($username, $password)
    {
        if ($this->_doLogin($username, $password))
        {
            $this->_jumpStartSession($username);
            $sSessionKey = randomChars(32);

            $session = new Sessions;
            $session->id = $sSessionKey;
            $session->expire = time() + Yii::app()->getConfig('iSessionExpirationTime');
            $session->data = $username;
            $session->save();

            return $sSessionKey;
        }
        else
            return array('status' => 'Invalid user name or password');
    }

    /**
    * Closes the RPC session
    *
    * @access public
    * @param string $sSessionKey
    * @return string
    */
    public function release_session_key($sSessionKey)
    {
        Sessions::model()->deleteAllByAttributes(array('id' => $sSessionKey));
        $criteria = new CDbCriteria;
        $criteria->condition = 'expire < ' . time();
        Sessions::model()->deleteAll($criteria);
        return 'OK';
    }


    /**
    * RPC routine to import a survey
    *
    * @access public
    * @param string $sSessionKey
    * @param string $sLSSData String containing the data of an LSS file
    * @param integer $DestSurveyID This is the new ID of the survey - if already used a random one will be taken instead
    * @return integer iSurveyID  - ID of the new survey
    */
    public function import_survey($sSessionKey, $sLSSData, $sNewSurveyName=NULL, $DestSurveyID=NULL)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
            {
                Yii::app()->loadHelper('admin/import');
                $aImportResults = XMLImportSurvey('', $sLSSData, $sNewSurveyName, $DestSurveyID);

                if (isset($aImportResults['error'])) return array('status' => 'Error: '.$aImportResults['error']);
                else
                {
                    return $aImportResults['newsid'];
                }
            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * RPC routine to activate a survey
    *
    * @access public
    * @param string $sSessionKey
    * @param string $sLSSData String containing the data of an LSS file
    * @param integer $DestSurveyID This is the new ID of the survey - if already used a random one will be taken instead
    * @return integer iSurveyID  - ID of the new survey
    */
    public function activate_survey($sSessionKey, $iSurveyID)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
            {
                Yii::app()->loadHelper('admin/activate');
                $aImportResults = activateSurvey($iSurveyID);

                if (isset($aImportResults['error'])) return array('status' => 'Error: '.$aImportResults['error']);
                else
                {
                    return $aImportResults;
                }
            }
            else
                return array('status' => 'No permission');
        }
    }


    /**
    * RPC routine to activate tokens
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iSurveyID ID of the survey where a token table will be created for
    * @param array $aAttributeFields  An array of integer describing any additional attribute fields
    * @return array Status=>OK when successfull, otherwise the error description
    */
    public function activate_tokens($sSessionKey, $iSurveyID, $aAttributeFields=array())
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
            {
                if (is_array($aAttributeFields) && count($aAttributeFields)>0)
                {
                    foreach ($aAttributeFields as &$sField)
                    {
                        $sField= intval($sField);
                        $sField='attribute_'.$sField;
                    }
                    $aAttributeFields=array_unique($aAttributeFields);
                }
                Yii::app()->loadHelper('admin/token');
                if (createTokenTable($iSurveyID, $aAttributeFields))
                {
                    return array('status' => 'OK');
                }
                else
                {
                    return array('status' => 'Token table could not be created');
                }
            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * RPC routine to delete a survey
    *
    * @access public
    * @param string $sSessionKey
    * @param int $sid
    * @return string
    * @throws Zend_XmlRpc_Server_Exception
    */
    public function delete_survey($sSessionKey, $sid)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasSurveyPermission($sid, 'survey', 'delete'))
            {
                Survey::model()->deleteSurvey($sid,true);
                return array('status' => 'OK');
            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * RPC routine to add a response to the survey table
    * Returns the id of the inserted survey response
    *
    * @access public
    * @param string $sSessionKey
    * @param int $sid
    * @param struct $aResponseData
    * @return int
    * @throws Zend_XmlRpc_Server_Exception
    */
    public function add_response($sSessionKey, $sid, $aResponseData)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasSurveyPermission($sid, 'response', 'create'))
            {
                if (!Yii::app()->db->schema->getTable('{{survey_' . $sid . '}}'))
                    return array('status' => 'No survey response table');

                //set required values if not set

                // @todo: This is part of the validation and should be done in the model instead
                if (!isset($aResponseData['submitdate']))
                    $aResponseData['submitdate'] = date("Y-m-d H:i:s");
                if (!isset($aResponseData['datestamp']))
                    $aResponseData['datestamp'] = date("Y-m-d H:i:s");
                if (!isset($aResponseData['startdate']))
                    $aResponseData['startdate'] = date("Y-m-d H:i:s");
                if (!isset($aResponseData['startlanguage']))
                    $aResponseData['startlanguage'] = getBaseLanguageFromSurveyID($iSurveyID);

                Survey_dynamic::sid($sid);
                $survey_dynamic = new Survey_dynamic;
                $result = $survey_dynamic->insert($aResponseData);

                if ($result)
                    return $survey_dynamic->primaryKey;
                else
                    return array('status' => 'Unable to add survey');
            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * RPC routine to add a participant to a token table
    * Returns the inserted data including additional new information like the Token entry ID and the token
    *
    * @access public
    * @param string $sSessionKey
    * @param int $sid
    * @param struct $participant_data
    * @param bool $create_token
    * @return array
    * @throws Zend_XmlRpc_Server_Exception
    */
    public function add_participants($sSessionKey, $sid, $participant_data, $create_token)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasSurveyPermission($sid, 'tokens', 'create'))
            {
                if (!Yii::app()->db->schema->getTable('{{tokens_' . $sid . '}}'))
                    return array('status' => 'No token table');

                $field_names = Yii::app()->db->schema->getTable('{{tokens_' . $sid . '}}')->getColumnNames();
                $field_names = array_flip($field_names);

                foreach ($participant_data as &$participant)
                {
                    foreach ($participant as $field_name => $value)
                        if (!isset($field_names[$field_name]))
                            unset($participant[$field_name]);

                        Tokens_dynamic::sid($sid);
                    $token = new Tokens_dynamic;

                    if ($token->insert($participant))
                    {
                        $new_token_id = $token->primaryKey;

                        if ($create_token)
                            $token_string = Tokens_dynamic::model()->createToken($new_token_id);
                        else
                            $token_string = '';

                        $participant = array_merge($participant, array(
                        'tid' => $new_token_id,
                        'token' => $token_string,
                        ));
                    }
                }

                return $participant_data;
            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * Tries to login with username and password
    *
    * @access protected
    * @param string $sUsername
    * @param mixed $sPassword
    * @return bool
    */
    protected function _doLogin($sUsername, $sPassword)
    {
        if (Failed_login_attempts::model()->isLockedOut())
            return false;

        $identity = new UserIdentity(sanitize_user($sUsername), $sPassword);

        if (!$identity->authenticate())
        {
            Failed_login_attempts::model()->addAttempt();
            return false;
        }
        else
            return true;
    }

    /**
    * Fills the session with necessary user info on the fly
    *
    * @access protected
    * @param string $sUsername
    * @return bool
    */
    protected function _jumpStartSession($username)
    {
        $aUserData = User::model()->findByAttributes(array('users_name' => $username))->attributes;

        $session = array(
        'loginID' => intval($aUserData['uid']),
        'user' => $aUserData['users_name'],
        'full_name' => $aUserData['full_name'],
        'htmleditormode' => $aUserData['htmleditormode'],
        'templateeditormode' => $aUserData['templateeditormode'],
        'questionselectormode' => $aUserData['questionselectormode'],
        'dateformat' => $aUserData['dateformat'],
        'adminlang' => 'en'
        );
        foreach ($session as $k => $v)
            Yii::app()->session[$k] = $v;
        Yii::app()->user->setId($aUserData['uid']);

        $this->controller->_GetSessionUserRights($aUserData['uid']);
        return true;
    }

    /**
    * This function checks if the XML-RPC session key is valid. If yes returns true, otherwise false and sends an error message with error code 1
    *
    * @access protected
    * @param string $sSessionKey
    * @return bool
    * @throws Zend_XmlRpc_Server_Exception
    */
    protected function _checkSessionKey($sSessionKey)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'expire < ' . time();
        Sessions::model()->deleteAll($criteria);
        $oResult = Sessions::model()->findByPk($sSessionKey);

        if (is_null($oResult))
            return false;
        else
        {
            $this->_jumpStartSession($oResult->data);
            return true;
        }
    }
}
