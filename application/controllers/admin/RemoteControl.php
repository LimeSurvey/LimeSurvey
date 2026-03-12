<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

class RemoteControl extends SurveyCommonAction
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
        Yii::import('application.helpers.remotecontrol.*');

        $setAccessControlHeader = Yii::app()->getConfig('add_access_control_header', 1);
        if ($setAccessControlHeader == 1) {
            header("Access-Control-Allow-Origin: *");
        }

        $oHandler = new remotecontrol_handle($this->controller);
        $RPCType = Yii::app()->getConfig("RPCInterface");
        if (Yii::app()->getRequest()->isPostRequest) {
            if ($RPCType == 'xml') {
                $cur_path = get_include_path();
                set_include_path($cur_path . PATH_SEPARATOR . APPPATH . 'helpers');
                // Yii::import was causing problems for some odd reason
                $this->xmlrpc = new Zend_XmlRpc_Server();
                $this->xmlrpc->sendArgumentsToAllMethods(false);
                Yii::import('application.libraries.LSZend_XmlRpc_Response_Http');
                $this->xmlrpc->setResponseClass('LSZend_XmlRpc_Response_Http');
                $this->xmlrpc->setClass($oHandler);
                $result = $this->xmlrpc->handle();
                if ($result instanceof LSZend_XmlRpc_Response_Http) {
                    $result->printXml();
                } else {
                    // a Zend_XmlRpc_Server_Fault with exception message from XMLRPC
                    echo $result;
                }
            } elseif ($RPCType == 'json') {
                Yii::app()->loadLibrary('LSjsonRPCServer');
                if (!isset($_SERVER['CONTENT_TYPE'])) {
                    $serverContentType = explode(';', (string) $_SERVER['HTTP_CONTENT_TYPE']);
                    $_SERVER['CONTENT_TYPE'] = reset($serverContentType);
                }
                LSjsonRPCServer::handle($oHandler);
            }
            foreach (App()->log->routes as $route) {
                $route->enabled = $route->enabled && !($route instanceof CWebLogRoute);
            }
            Yii::app()->session->destroy();
            exit;
        } else {
            // Disabled output of API methods for now
            if (Yii::app()->getConfig("rpc_publish_api") == true && in_array($RPCType, array('xml', 'json'))) {
                $reflector = new ReflectionObject($oHandler);
                foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    /* @var $method ReflectionMethod */
                    if (substr($method->getName(), 0, 1) !== '_') {
                        $list[$method->getName()] = array(
                            'description' => $method->getDocComment(),
                            'parameters'  => $method->getParameters(),
                        );
                    }
                }
                ksort($list);
                $aData['method'] = $RPCType;
                $aData['list'] = $list;
                $this->renderWrappedTemplate('remotecontrol', array('index_view'), $aData);
            }
        }
    }

    /**
     * Simple procedure to test most RPC functions
     *
     */
    public function test()
    {
        // Enable if you want to test this function
        $enabled = false;
        if ($enabled) {
            $RPCType = Yii::app()->getConfig("RPCInterface");
            $serverUrl = App()->createAbsoluteUrl('/admin/remotecontrol');
            $sFileToImport = dirname((string) Yii::app()->basePath) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'demosurveys' . DIRECTORY_SEPARATOR . 'ls205_sample_survey_english.lss';

            if ($RPCType == 'xml') {
                $cur_path = get_include_path();
                set_include_path($cur_path . PATH_SEPARATOR . APPPATH . 'helpers');
                $client = new Zend_XmlRpc_Client($serverUrl);
                // Increase timeout (default is 10 seconds). Importing the survey may take a while.
                $client->getHttpClient()->setConfig(['timeout' => 300]);
            } elseif ($RPCType == 'json') {
                Yii::app()->loadLibrary('jsonRPCClient');
                $client = new jsonRPCClient($serverUrl);
                // Set $client = new jsonRPCClient($serverUrl, true); to activate debug output
            } else {
                die('RPC interface not activated in global settings');
            }


            $sSessionKey = $client->call('get_session_key', array('admin', 'password'));
            if (is_array($sSessionKey)) {
                echo $sSessionKey['status'];
                die();
            } else {
                echo 'Retrieved session key' . '<br>';
            }

            $sLSSData = base64_encode(file_get_contents($sFileToImport));
            $iSurveyID = $client->call('import_survey', array($sSessionKey, $sLSSData, 'lss', 'Test import by JSON_RPC'));
            echo 'Created new survey SID:' . $iSurveyID . '<br>';

            $aResult = $client->call('activate_survey', array($sSessionKey, $iSurveyID));
            if ($aResult['status'] == 'OK') {
                echo 'Survey ' . $iSurveyID . ' successfully activated.<br>';
            }
            $aResult = $client->call('activate_tokens', array($sSessionKey, $iSurveyID, array(1, 2)));
            if ($aResult['status'] == 'OK') {
                echo 'Tokens for Survey ID ' . $iSurveyID . ' successfully activated.<br>';
            }
            $aResult = $client->call('set_survey_properties', array($sSessionKey, $iSurveyID, array('admin' => 'Admin name')));
            if (!array_key_exists('status', $aResult)) {
                echo 'Modified survey settings for survey ' . $iSurveyID . '<br>';
            }
            $aResult = $client->call('add_language', array($sSessionKey, $iSurveyID, 'ar'));
            if ($aResult['status'] == 'OK') {
                echo 'Added Arabian as additional language' . '<br>';
            }
            $aResult = $client->call('set_language_properties', array($sSessionKey, $iSurveyID, array('surveyls_welcometext' => 'An Arabian welcome text!'), 'ar'));
            if ($aResult['status'] == 'OK') {
                echo 'Modified survey locale setting welcometext for Arabian in survey ID ' . $iSurveyID . '<br>';
            }

            $aResult = $client->call('delete_language', array($sSessionKey, $iSurveyID, 'ar'));
            if ($aResult['status'] == 'OK') {
                echo 'Removed Arabian as additional language' . '<br>';
            }
            $aResult = $client->call('add_participants', array($sSessionKey, $iSurveyID, array(array('firstname' => 'Some', 'lastname' => 'Body', 'email' => 'somebody@test.com'))));
            if (!array_key_exists('status', $aResult)) {
                echo 'Added a participant to survey ' . $iSurveyID . '<br>';
            }
            $aResult = $client->call('set_participant_properties', array($sSessionKey, $iSurveyID, array('email' => 'somebody@test.com'), array('lastname' => 'One', 'email' => 'someone@test.com')));
            if (!array_key_exists('status', $aResult)) {
                echo 'Modified participant properties in survey ' . $iSurveyID . '<br>';
            }

            //Very simple example to export responses as Excel file
            //$aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'xls'));
            //$aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'pdf'));
            //$aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'doc'));
            $aResult = $client->call('export_responses', array($sSessionKey, $iSurveyID, 'csv'));
            //file_put_contents('test.xls',base64_decode(chunk_split($aResult)));

            $aResult = $client->call('delete_survey', array($sSessionKey, $iSurveyID));
            echo 'Deleted survey SID:' . $iSurveyID . '-' . $aResult['status'] . '<br>';

            // Release the session key - close the session
            $Result = $client->call('release_session_key', array($sSessionKey));
            echo 'Closed the session' . '<br>';
        }
    }
}
