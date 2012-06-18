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
 * 	$Id$
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
        $cur_path = get_include_path();

        set_include_path($cur_path . ':' . APPPATH . 'helpers');

        // Yii::import was causing problems for some odd reason
        require_once('Zend/XmlRpc/Server.php');
        require_once('Zend/XmlRpc/Server/Exception.php');
        require_once('Zend/XmlRpc/Value/Exception.php');

        $this->xmlrpc = new Zend_XmlRpc_Server();
        $this->xmlrpc->sendArgumentsToAllMethods(false);
        $this->xmlrpc->setClass('remotecontrol_handle', '', $this->controller);
        echo $this->xmlrpc->handle();
        exit;
    }

    /**
     * Couldn't include test routine as it'd require a couple more Zend libraries
     * Instead use PHP XMLRPC Debugger with the following payloads to test routines
     * Adjusts some values accordingly
     *
     * get_session_key : Use this to obtain session_key for other call
          <param>
              <value><string>username</string></value>
          </param>
          <param>
              <value><string>password</string></value>
          </param>
     *
     * add_participants
          <param>
              <value><string>session_key</string></value>
          </param>
          <!-- Survey id -->
          <param>
              <value><i4>552489</i4></value>
          </param>
          <!-- Participants information -->
          <param>
              <value><array><data><value><struct><member><name>firstname</name><value><string>firstname1</string></value></member><member><name>lastname</name><value><string>lastname1</string></value></member><member><name>dummy</name><value><string>lastname1</string></value></member></struct></value></data></array></value>
          </param>
     *
     * delete_survey
          <param>
              <value><string>session_key</string></value>
          </param>
          <!-- Survey id --.
          <param>
              <value><i4>78184</i4></value>
          </param>
     */
}

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
     * XML-RPC routine to create a session key
     *
     * @access public
     * @param string $username
     * @param string $password
     * @return string
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function get_session_key($username, $password)
    {
        if ($this->_doLogin($username, $password))
        {
            $this->_jumpStartSession($username);
            $session_key = randomChars(32);

            $session = new Sessions;
            $session->id = $session_key;
            $session->expire = time() + Yii::app()->getConfig('iSessionExpirationTime');
            $session->data = $username;
            $session->save();

            return $session_key;
        }
        else
            throw new Zend_XmlRpc_Server_Exception('Login failed', 1);
    }

    /**
     * Closes the RPC session
     *
     * @access public
     * @param string $session_key
     * @return string
     */
    public function release_session_key($session_key)
    {
        Sessions::model()->deleteAllByAttributes(array('id' => $session_key));
        $criteria = new CDbCriteria;
        $criteria->condition = 'expire < ' . time();
        Sessions::model()->deleteAll($criteria);
        return 'OK';
    }

    /**
     * XML-RPC routine to delete a survey
     *
     * @access public
     * @param string $session_key
     * @param int $sid
     * @return string
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function delete_survey($session_key, $sid)
    {
        if ($this->_checkSessionKey($session_key))
        {
            if (hasSurveyPermission($sid, 'survey', 'delete'))
            {
                Survey::model()->deleteAllByAttributes(array('sid' => $sid));
                rmdirr(Yii::app()->getConfig("uploaddir") . '/surveys/' . $sid);
                return array('status' => 'OK');
            }
            else
                throw new Zend_XmlRpc_Server_Exception('No permission', 2);
        }
    }

    /**
     * XML-RPC routing to add a response to the survey table
     * Returns the id of the inserted survey response
     *
     * @access public
     * @param string $session_key
     * @param int $sid
     * @param struct $aResponseData
     * @return int
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function add_response($session_key, $sid, $aResponseData)
    {
        if ($this->_checkSessionKey($session_key))
        {
            if (hasSurveyPermission($sid, 'response', 'create'))
            {
                if (!Yii::app()->db->schema->getTable('{{survey_' . $sid . '}}'))
                    throw new Zend_XmlRpc_Server_Exception('No survey response table', 12);

                //set required values if not set
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
                    throw new Zend_XmlRpc_Server_Exception('Unable to add survey', 13);
            }
            else
                throw new Zend_XmlRpc_Server_Exception('No permission', 2);
        }
    }

    /**
     * XML-RPC routine to add a participant to a token table
     * Returns the inserted data including additional new information like the Token entry ID and the token
     *
     * @access public
     * @param string $session_key
     * @param int $sid
     * @param struct $participant_data
     * @param bool $create_token
     * @return array
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function add_participants($session_key, $sid, $participant_data, $create_token)
    {
        if ($this->_checkSessionKey($session_key))
        {
            if (hasSurveyPermission($sid, 'tokens', 'create'))
            {
                if (!Yii::app()->db->schema->getTable('{{tokens_' . $sid . '}}'))
                    throw new Zend_XmlRpc_Server_Exception('No token table', 11);

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
                throw new Zend_XmlRpc_Server_Exception('No permission', 2);
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
     * @param string $session_key
     * @return bool
     * @throws Zend_XmlRpc_Server_Exception
     */
    protected function _checkSessionKey($session_key)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'expire < ' . time();
        Sessions::model()->deleteAll($criteria);
        $oResult = Sessions::model()->findByPk($session_key);

        if (is_null($oResult))
            throw new Zend_XmlRpc_Server_Exception('Invalid session key', 3);
        else
        {
            $this->_jumpStartSession($oResult->data);
            return true;
        }
    }
}
