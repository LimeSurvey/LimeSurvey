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

    /**
     * Return the referal url,
     * it's used for the "close" buttons, and the "save and close" buttons
     * So it checks if the referrer url is the same than the current url to avoid looping.
     * If it the case, a paramater can be set to tell what referrer to return.
     * If the referrer is an external url, Yii return by default the current url.
     *
     * To avoid looping between two urls (like simpleStatistics <=> Expert Statistics),
     * it can be necessary to check if the referrer contains a specific word (an action in general)
     * So if you want to forbid a return to a certain page, just provide an alternative url, and the forbidden key world
     *
     * Not all "close" and "save and close" buttons should not use it.
     * Only close button for pages that can be accessed since different places.
     * eg: edit question, that can be accessed from question list or question
     *
     * @param $sAlternativeUrl string, the url to return if referrer url is the same than current url.
     * @param $aForbiddenWordsInUrl array, an array containing forbidden words in url
     * @return string if success, else null
     */
    public function getUrlReferrer($sAlternativeUrl=null, $aForbiddenWordsInUrl=array())
    {

       $referrer = parent::getUrlReferrer();
       $baseReferrer    = str_replace(Yii::app()->getBaseUrl(true), "", $referrer);
       $baseRequestUri  = str_replace(Yii::app()->getBaseUrl(), "", Yii::app()->request->requestUri);
       $referrer = ($baseReferrer != $baseRequestUri)?$referrer:null;

       // Checks if the alternative url should be used
       if(isset($sAlternativeUrl))
       {
           // Use alternative url if the referrer is equal to current url.
           if(is_null($referrer))
           {
               $referrer = $sAlternativeUrl;
           }

           // Use alternative url if a forbidden word appears in the referrer
           foreach($aForbiddenWordsInUrl as $sForbiddenWord)
           {
               if (strpos($referrer, $sForbiddenWord) !== false)
               {
                   $referrer = $sAlternativeUrl;
               }
           }
       }

       return $referrer;
    }

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
