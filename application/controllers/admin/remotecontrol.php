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
                set_include_path($cur_path.PATH_SEPARATOR.APPPATH.'helpers');
                // Yii::import was causing problems for some odd reason
                require_once('Zend/XmlRpc/Server.php');
                require_once('Zend/XmlRpc/Server/Exception.php');
                require_once('Zend/XmlRpc/Value/Exception.php');
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
                    $serverContentType = explode(';', $_SERVER['HTTP_CONTENT_TYPE']);
                    $_SERVER['CONTENT_TYPE'] = reset($serverContentType);
                }
                LSjsonRPCServer::handle($oHandler);
            }
            foreach (App()->log->routes as $route) {
                $route->enabled = $route->enabled && !($route instanceOf CWebLogRoute);
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
                $this->_renderWrappedTemplate('remotecontrol', array('index_view'), $aData);
            }
        }
        Yii::app()->session->destroy();
    }
}
