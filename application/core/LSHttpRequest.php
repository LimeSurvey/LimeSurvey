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
*/


/**
 * Description of HttpRequest
 *
 *
 * Used in LSYii_Application.php
 * <pre>
 *    'request'=>array(
 *        'class'=>'HttpRequest',
 *        'noCsrfValidationRoutes'=>array(
 *            '^services/wsdl.*$'
 *        ),
 *        'enableCsrfValidation'=>true,
 *        'enableCookieValidation'=>true,
 *    ),
 * </pre>
 *
 * Every route will be interpreted as a regex pattern.
 *
 */
class LSHttpRequest extends CHttpRequest {
    public $noCsrfValidationRoutes = array();

    protected function normalizeRequest(){
        parent::normalizeRequest();
        
        if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') return;

        $route = Yii::app()->getUrlManager()->parseUrl($this);
        if($this->enableCsrfValidation){
            foreach($this->noCsrfValidationRoutes as $cr){
                if(preg_match('#'.$cr.'#', $route)){
                    Yii::app()->detachEventHandler('onBeginRequest',
                        array($this,'validateCsrfToken'));
                    Yii::trace('Route "'.$route.' passed without CSRF validation');
                    break; // found first route and break
                }
            }
        }
    }


    public function getPathInfo()
    {
        if($this->_pathInfo===null)
        {
            $pathInfo=$this->getRequestUri();

            if(($pos=strpos($pathInfo,'?'))!==false)
                $pathInfo=substr($pathInfo,0,$pos);

            $pathInfo=$this->decodePathInfo($pathInfo);

            $scriptUrl=$this->getScriptUrl();
            $baseUrl=$this->getBaseUrl();
            if(strpos($pathInfo,$scriptUrl)===0)
                $pathInfo=substr($pathInfo,strlen($scriptUrl));
            elseif($baseUrl==='' || strpos($pathInfo,$baseUrl)===0)
                $pathInfo=substr($pathInfo,strlen($baseUrl));
            elseif(strpos($_SERVER['PHP_SELF'],$scriptUrl)===0)
                $pathInfo=substr($_SERVER['PHP_SELF'],strlen($scriptUrl));
            else
                throw new CException(Yii::t('yii','CHttpRequest is unable to determine the path info of the request.'));

            if($pathInfo==='/')
                $pathInfo='';
            elseif(!empty($pathInfo) && $pathInfo[0]==='/')
                $pathInfo=substr($pathInfo,1);

            if(($posEnd=strlen($pathInfo)-1)>0 && $pathInfo[$posEnd]==='/')
                $pathInfo=substr($pathInfo,0,$posEnd);

            $this->_pathInfo=$pathInfo;
        }
        return $this->_pathInfo;
    }

}