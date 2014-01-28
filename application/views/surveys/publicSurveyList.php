<?php
   
    $list = '';
    foreach($publicSurveys as $survey)
    {
        $list .= CHtml::openTag('li');

        $list .= CHtml::link($survey->localizedTitle, array('survey/index', 'sid' => $survey->sid, 'lang' => App()->lang->langcode), array('class' => 'surveytitle'));
        if ($survey->publicstatistics == "Y")
        {
            $list .= CHtml::link('(' . App()->lang->gT('View statistics') . ')', array('statistics_user/action', 'surveyid' => $survey->sid));
        }
        $list .= CHtml::closeTag('li');
        
    }
    if (!empty($futureSurveys))
    {
        $list .= "</ul><div class=\"survey-list-heading\">".  gT("Following survey(s) are not yet active but you can register for them.")."</div><ul>";
        foreach($futureSurveys as $survey)
        {
            $list .= CHtml::openTag('li');

            $list .= CHtml::link($survey->localizedTitle, array('survey/index', 'sid' => $survey->sid, 'lang' => App()->lang->langcode), array('class' => 'surveytitle'));
            $list .= CHtml::closeTag('li');
            $list .= CHtml::tag('div', array(
                'data-regformsurvey' => $survey->sid,

            ));
        }
    }
    $data['surveylist'] = array(
        "nosid"=> "",
        "contact"=>sprintf(App()->lang->gT("Please contact %s ( %s ) for further assistance."),Yii::app()->getConfig("siteadminname"),encodeEmail(Yii::app()->getConfig("siteadminemail"))),
        "listheading"=> App()->lang->gT("The following surveys are available:"),
        "list"=> $list
    );
    $data['templatedir'] = getTemplatePath(Yii::app()->getConfig("defaulttemplate"));
    $data['templateurl'] = getTemplateURL(Yii::app()->getConfig("defaulttemplate"))."/";
    $data['templatename'] = Yii::app()->getConfig("defaulttemplate");
    $data['sitename'] = Yii::app()->getConfig("sitename");
    $data['languagechanger'] = makeLanguageChanger(App()->lang->langcode);

    //A nice exit
    sendCacheHeaders();
    doHeader();

    echo templatereplace(file_get_contents(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/startpage.pstpl"),array(),$data,'survey['.__LINE__.']');
    echo templatereplace(file_get_contents(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/surveylist.pstpl"),array(),$data,'survey['.__LINE__.']');
    echo templatereplace(file_get_contents(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/endpage.pstpl"),array(),$data,'survey['.__LINE__.']');
    doFooter();
?>