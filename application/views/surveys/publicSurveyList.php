<?php
    $outputSurveys = 0;
    $list = "<div class='container'>";
    $list .= "<div class='row'>";
    $divideToggle = true;

    foreach($publicSurveys as $survey)
    {
         $outputSurveys++;
                //echo "IKI :";var_dump( $survey->localizedTitle);
        $divider = ($divideToggle ? " vertical-divider right " : "");
        if ($survey->publicstatistics == "Y")
        {
            $statistics = "<div class='col-md-1 col-sm-2 col-xs-2 no-divide ls-custom-padding five ".$divider."'>";
            $statistics .= CHtml::link('<span class="fa fa-bar-chart" aria-hidden="true"></span><span class="sr-only">'. gT('View statistics') .'</span>',
                        array('statistics_user/action', 'surveyid' => $survey->sid,'language' => App()->language),
                        array(
                            'class'=>'view-stats btn btn-success btn-block',
                            'title'=>gT('View statistics'),
                            'data-toggle'=>'tooltip',
                        )
                    );
            $statistics .= "</div>";
            $list .= "<div class='col-md-5 col-sm-10 col-xs-10 ls-custom-padding five'>";
        }
        else
        {
            $statistics = "";
            $list .= "<div class='col-md-6 col-xs-12 ls-custom-padding five ".$divider."'>";
        }
        //@TODO Make $allowTooltips a global configuration setting
        $allowTooltips = "Y";

        $content = $survey->localizedTitle;
        $list .= CHtml::link(
            $content,
            array(
                'survey/index',
                'sid' => $survey->sid,
                'lang' => App()->language,
                'encode' => false
                ),
                array(
                    'class' => 'surveytitle btn btn-primary btn-block'
                )
                );
        $list .= "</div>";
        $list .= $statistics;
        $divideToggle = !$divideToggle;
    }

    $list .= "</div>";
    $list .= "</div>";

    if (!empty($futureSurveys))
    {
        $list .= "<div class=\"survey-list-heading\">".  gT("Following survey(s) are not yet active but you can register for them.")."</div>";
        $list .= "<div class='container'>";
        $list .= "<div class='row'>";
        foreach($futureSurveys as $survey)
        {
            $outputSurveys++;
            $list .= CHtml::openTag('div', array('class'=>'col-xs-12'));
            $list .= CHtml::link($survey->localizedTitle, array('survey/index', 'sid' => $survey->sid, 'lang' => App()->language), array('class' => 'surveytitle'));
            $list .= CHtml::closeTag('div');
            $list .= CHtml::tag('div', array(
                'data-regformsurvey' => $survey->sid,
                'class' => 'col-xs-12'
            ));
        }
    }

    $listheading="<div class='container'>
                    <div class='h3'>
                    ".gT("The following surveys are available:")."
                    </div>
                    </div>";
    if( $outputSurveys==0)
    {
        $list=CHtml::openTag('div',array('class'=>'col-xs-12')).gT("No available surveys").CHtml::closeTag('div');
    }
    $data['surveylist'] = array(
        "nosid"=> "",
        "contact"=> sprintf(gT("Please contact %s ( %s ) for further assistance."),
            Yii::app()->getConfig("siteadminname"),
            encodeEmail(Yii::app()->getConfig("siteadminemail"))
        ),
        "listheading"=> $listheading,
        "list"=> $list,
    );

    $oTemplate = Template::model()->getInstance("default");

    $data['templatedir'] = Template::getTemplatePath(Yii::app()->getConfig("defaulttemplate"));
    $data['templateurl'] = Template::getTemplateURL(Yii::app()->getConfig("defaulttemplate"))."/";
    $data['templatename'] = $oTemplate->name;
    $data['sitename'] = Yii::app()->getConfig("sitename");
    $data['languagechanger'] = makeLanguageChanger(App()->language);

    //A nice exit
    sendCacheHeaders();
    doHeader();
    // Javascript Var
    $aLSJavascriptVar=array();
    $aLSJavascriptVar['bFixNumAuto']=(int)(bool)Yii::app()->getConfig('bFixNumAuto',1);
    $aLSJavascriptVar['bNumRealValue']=(int)(bool)Yii::app()->getConfig('bNumRealValue',0);
    if(isset($thissurvey['surveyls_numberformat']))
    {
        $radix=getRadixPointData($thissurvey['surveyls_numberformat']);
    }
    else
    {
        $aLangData=getLanguageData();
        $radix=getRadixPointData($aLangData[ Yii::app()->getConfig('defaultlang')]['radixpoint']);// or App()->language . defaultlang  ensure it's same for each language ?
    }
    $aLSJavascriptVar['sLEMradix']=$radix['separator'];
    $sLSJavascriptVar="LSvar=".json_encode($aLSJavascriptVar);
    App()->clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);
    App()->clientScript->registerScript('setJsVar',"setJsVar();",CClientScript::POS_BEGIN);// Ensure all js var is set before rendering the page (User can click before $.ready)
    App()->getClientScript()->registerPackage('jqueryui');
    App()->getClientScript()->registerPackage('jquery-touch-punch');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."survey_runtime.js");
    useFirebug();

    echo templatereplace(file_get_contents($oTemplate->viewPath."/startpage.pstpl"),array(),$data,'survey['.__LINE__.']');
    echo templatereplace(file_get_contents($oTemplate->viewPath."/surveylist.pstpl"),array(),$data,'survey['.__LINE__.']');
    echo templatereplace(file_get_contents($oTemplate->viewPath."/endpage.pstpl"),array(),$data,'survey['.__LINE__.']');
    doFooter();
?>
