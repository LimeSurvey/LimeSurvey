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

}