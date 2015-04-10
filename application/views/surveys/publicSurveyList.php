<?php
    echo TbHtml::tag('h1', [], App()->name);

    
    foreach($publicSurveys as $survey)
    {
        $list .= CHtml::openTag('li');

        $list .= CHtml::link($survey->localizedTitle, array('survey/index', 'sid' => $survey->sid, 'lang' => App()->language), array('class' => 'surveytitle'));
        if ($survey->publicstatistics == "Y")
        {
            $list .= CHtml::link('(' . gT('View statistics') . ')', array('statistics_user/action', 'surveyid' => $survey->sid,'language' => App()->language));
        }
        $list .= CHtml::closeTag('li');

    }
    if (!empty($futureSurveys))
    {
        $list .= "</ul><div class=\"survey-list-heading\">".  gT("Following survey(s) are not yet active but you can register for them.")."</div><ul>";
        foreach($futureSurveys as $survey)
        {
            $list .= CHtml::openTag('li');
            $list .= CHtml::link($survey->localizedTitle, array('survey/index', 'sid' => $survey->sid, 'lang' => App()->language), array('class' => 'surveytitle'));
            $list .= CHtml::closeTag('li');
            $list .= CHtml::tag('div', array(
                'data-regformsurvey' => $survey->sid,

            ));
        }
    }
    if(empty($list)) {
        $list=CHtml::openTag('li',array('class'=>'surveytitle')).gT("No available surveys").CHtml::closeTag('li');
    }
    echo $list;
    $data['surveylist'] = array(
        "nosid"=> "",
        "contact"=> sprintf(gT("Please contact %s ( %s ) for further assistance."),
            Yii::app()->getConfig("siteadminname"),
            encodeEmail(Yii::app()->getConfig("siteadminemail"))
        ),
        "listheading"=> gT("The following surveys are available:"),
        "list"=> $list,
    );
    
    $data['languagechanger'] = makeLanguageChanger(App()->language);
    echo App()->format->format(trim(App()->getConfig("siteadminemail")), 'email');