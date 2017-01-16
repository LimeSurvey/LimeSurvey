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
class Load_answers {

    function run($args) {
        extract($args);

        /** args is :
         * surveyid
         * aLoadErrorMsg
         * clienttoken
         */
        $aData=array(
            'surveyid'=>$surveyid,
            'clienttoken'=>$clienttoken, // send in caller, call only one time
        );
        $sTemplate=Survey::model()->findByPk($surveyid)->template;
        $oTemplate = Template::model()->getInstance($sTemplate);
        /* Construction of data for templatereplace */
        $aReplacements['LOADHEADING'] = App()->getController()->renderPartial("/survey/frontpage/loadForm/heading",array(),true);
        $aReplacements['LOADMESSAGE'] = App()->getController()->renderPartial("/survey/frontpage/loadForm/message",array(),true);
        if(!empty($aLoadErrorMsg)){
                $aReplacements['LOADERROR'] = App()->getController()->renderPartial("/survey/frontpage/loadForm/error",array('aLoadErrorMsg'=>$aLoadErrorMsg),true);
        }else{
                $aReplacements['LOADERROR'] = "";
        }
        if(function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', Survey::model()->findByPk($surveyid)->usecaptcha)){
                $captcha=Yii::app()->getController()->createUrl('/verification/image',array('sid'=>$surveyid));
        }else{
                $captcha=null;
        }
        $loadForm  = CHtml::beginForm(array("/survey/index","sid"=>$surveyid), 'post',array('id'=>'form-load'));
        $loadForm .= App()->getController()->renderPartial("/survey/frontpage/loadForm/form",array('captcha'=>$captcha),true);
        if ($clienttoken)
        {
            $loadForm .= CHtml::hiddenField('token',$clienttoken);
        }

        $redata = compact(array_keys(get_defined_vars()));
        $sTemplatePath=$_SESSION['survey_'.$surveyid]['templatepath'];
        sendCacheHeaders();
        doHeader();

        $oTemplate = Template::model()->getInstance(null, $surveyid);

        echo templatereplace(file_get_contents($oTemplate->viewPath."startpage.pstpl"),array(),$redata);

        echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
        ."\t<script type='text/javascript'>\n"
        ."function checkconditions(value, name, type, evt_type)\n"
        ."\t{\n"
        ."\t}\n"
        ."\t</script>\n\n";

        echo CHtml::form(array("/survey/index","sid"=>$surveyid), 'post')."\n";
        echo templatereplace(file_get_contents($oTemplate->viewPath."load.pstpl"),array(),$redata);

        //PRESENT OPTIONS SCREEN (Replace with Template Later)
        //END
        echo "<input type='hidden' name='loadall' value='reload' />\n";
        if (isset($clienttoken) && $clienttoken != "")
        {
            echo CHtml::hiddenField('token',$clienttoken);
        }
        $loadForm .= CHtml::endForm();
        $aReplacements['LOADFORM'] = $loadForm;

        $content = templatereplace(file_get_contents($oTemplate->pstplPath."load.pstpl"),$aReplacements,$aData);
        App()->getController()->layout="survey";
        App()->getController()->sTemplate=$sTemplate;
        App()->getController()->aGlobalData=$aData;
        App()->getController()->aReplacementData=$aReplacements;

        App()->getController()->render("/survey/system/display",array(
            'content'=>$content,
        ));
        Yii::app()->end();


    }
}
