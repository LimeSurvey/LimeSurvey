<?php

    /* Preparing some array
    /* Template list : find User template, allways add existing template */
    $aTemplateOptions=array();
    foreach (array_keys(getTemplateList()) as $sTemplateName) {
        if(Permission::model()->hasTemplatePermission($sTemplateName) || htmlspecialchars($sTemplateName) == $esrow['template'])
            $aTemplateOptions[$sTemplateName]=$sTemplateName;
    }
    /* showxquestion */
    $sValShowxquestions=$esrow['showxquestions'];
    switch (Yii::app()->getConfig('showxquestions')) 
    {
        case 'show':
            $aShowxquestionsOptions=array("Y"=>gT('Yes (Forced by the system administrator)','unescaped'));
            $bDisableShowxquestions=true;
            $sValShowxquestions="Y";
            break;
        case 'hide':
            $aShowxquestionsOptions=array("N"=>gT('No (Forced by the system administrator)','unescaped'));
            $bDisableShowxquestions=true;
            $sValShowxquestions="N";
            break;
        case 'choose':
        default:
            $aShowxquestionsOptions=array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped'));
            $bDisableShowxquestions=false;
            break;
    }
    /* showgroupinfo */
    $sValShowgroupinfo=$esrow['showgroupinfo'];
    switch (Yii::app()->getConfig('showgroupinfo')) 
    {
        case 'show':
            $aShowgroupinfoOptions=array("B"=>gT('Show both (Forced by the system administrator)','unescaped'));
            $bDisableShowgroupinfo=true;
            $sValShowgroupinfo="B";
            break;
        case 'name':
            $aShowgroupinfoOptions=array("N"=>gT('Show group name only (Forced by the system administrator)','unescaped'));
            $bDisableShowgroupinfo=true;
            $sValShowgroupinfo="N";
            break;
        case 'description':
            $aShowgroupinfoOptions=array("D"=>gT('Show group description only (Forced by the system administrator)','unescaped'));
            $bDisableShowgroupinfo=true;
            $sValShowgroupinfo="D";
            break;
        case 'none':
            $aShowgroupinfoOptions=array("X"=>gT("Hide both (Forced by the system administrator)",'unescaped'));
            $bDisableShowgroupinfo=true;
            $sValShowgroupinfo="X";
            break;
        case 'choose':
        default:
            $aShowgroupinfoOptions=array(
                "B"=>gT("Show both",'unescaped'),
                "N"=>gT("Show group name only",'unescaped'),
                "D"=>gt("Show group description only",'unescaped'),
                "X"=>gt("Hide both",'unescaped')
            );
            $bDisableShowgroupinfo=false;
            break;
    }
    /* showqnumcode */
    $sValShowqnumcode=$esrow['showqnumcode'];
    switch (Yii::app()->getConfig('showqnumcode')) 
    {
        case 'show':
            $aShowqnumcodeOptions=array("B"=>gT('Show both (Forced by the system administrator)','unescaped'));
            $bDisableShowqnumcode=true;
            $sValShowqnumcode="B";
            break;
        case 'number':
            $aShowqnumcodeOptions=array("N"=>gT('Show question number only (Forced by the system administrator)','unescaped'));
            $bDisableShowqnumcode=true;
            $sValShowqnumcode="N";
            break;
        case 'code':
            $aShowqnumcodeOptions=array("C"=>gT('Show question code only (Forced by the system administrator)','unescaped'));
            $bDisableShowqnumcode=true;
            $sValShowqnumcode="C";
            break;
        case 'none':
            $aShowqnumcodeOptions=array("X"=>gT('Hide both (Forced by the system administrator)','unescaped'));
            $bDisableShowqnumcode=true;
            $sValShowqnumcode="X";
            break;
        case 'choose':
        default:
            $aShowqnumcodeOptions=array(
                "B"=>gT('Show both','unescaped'),
                "N"=>gT('Show question number only','unescaped'),
                "C"=>gT('Show question code only','unescaped'),
                "X"=>gT('Hide both'),
            );
            $bDisableShowqnumcode=false;
            if(!in_array($sValShowqnumcode,array("B","N","C","X")))
                $sValShowqnumcode="X";
            break;
    }
    /* shownoanswer */
    $sValShownoanswer=$esrow['shownoanswer'];
    $shownoanswer=!is_null(Yii::app()->getConfig('shownoanswer')) ? Yii::app()->getConfig('shownoanswer') : 1;
    switch ($shownoanswer) 
    {
        case '1':
            $aShownoanswerOptions=array("Y"=>gT('Yes (Forced by the system administrator)','unescaped'));
            $bDisableShownoanswer=true;
            $sValShownoanswer="Y";
            break;
        case '0':
            $aShownoanswerOptions=array("N"=>gT('No (Forced by the system administrator)','unescaped'));
            $bDisableShownoanswer=true;
            $sValShownoanswer="N";
            break;
        case '2':
        default:
            $aShownoanswerOptions=array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped'));
            $bDisableShownoanswer=false;
            break;
    }
    /* Need some javascript var */
    $sTemplateUrlScriptVar="standardtemplaterooturl='".Yii::app()->getConfig('standardtemplaterooturl')."'\n"
                          ."templaterooturl='".Yii::app()->getConfig('usertemplaterooturl')."'\n";
    Yii::app()->getClientScript()->registerScript("sTemplateUrlScriptVar", $sTemplateUrlScriptVar, CClientScript::POS_BEGIN);
    /* Presentation & navigation settings */
    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'id'=>'presentation',
        'title'=>gT("Presentation & navigation"),
        'form' => false,
        'formHtmlOptions'=>array(
            'class'=>'form-core',
        ),
        'settings' => array(
            'format'=>array(
                'type'=>'select',
                'label'=>gT("Format"),
                'options'=>array(
                    "S"=>gT("Question by Question",'unescaped'),
                    "G"=>gT("Group by Group",'unescaped'),
                    "A"=>gT("All in one",'unescaped'),
                ),
                'current'=>$esrow['format'],
            ),
            'template'=>array(
                'type'=>'select',
                'label'=>gT("Template"),
                'options'=>$aTemplateOptions,
                'current'=>$esrow['template'],
                'selectOptions'=>array(
                    'minimumResultsForSearch'=>15,
                ),
                'events'=>array(
                    'change'=>'js: function(event) {  templatechange(event.val) } ',
                ),
            ),
            'preview'=>array(
                'type'=>'info',
                'label'=>gT("Template preview"),
                'content'=>CHtml::image(getTemplateURL($esrow['template']).'/preview.png',gT("Template preview image"),array('id'=>'preview','class'=>'img-thumbnail')),
            ),
            'showwelcome'=>array(
                'type'=>'select',
                'label'=>gT("Show welcome screen?"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['showwelcome'],
            ),
            'navigationdelay'=>array(
                'type'=>'int',
                'label'=>gT("Navigation delay (seconds)"),
                'htmlOptions'=>array(
                    'style'=>'width:12em',
                ),
                'current'=>$esrow['navigationdelay'],
            ),
            'allowprev'=>array(
                'type'=>'select',
                'label'=>gT("Show [Previous] button"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['allowprev'],
            ),
            'questionindex'=>array(
                'type'=>'select',
                'label'=>gT("Show question index / allow jumping"),
                'options'=>array(
                    0 => gT('Disabled','unescaped'),
                    1 => gT('Incremental','unescaped'),
                    2 => gT('Full','unescaped'),
                ),
                'current'=>$esrow['questionindex'],
            ),
            'nokeyboard'=>array(// This settings MUST be moved to an external plugin : including a SUrvey parameteres  register a script, nothing else
                'type'=>'select',
                'label'=>gT("Keyboard-less operation"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>($esrow['nokeyboard'] ? $esrow['nokeyboard']:"N"),
            ),
            'showprogress'=>array(
                'type'=>'select',
                'label'=>gT("Show progress bar"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['showprogress'],
            ),
            'printanswers'=>array(
                'type'=>'select',
                'label'=>gT("Participants may print answers?"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['printanswers'],
            ),
            'publicstatistics'=>array(
                'type'=>'select',
                'label'=>gT("Public statistics?"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['publicstatistics'],
            ),
            'publicgraphs'=>array(
                'type'=>'select',
                'label'=>gT("Show graphs in public statistics?"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['publicgraphs'],
            ),
            'autoredirect'=>array(
                'type'=>'select',
                'label'=>gT("Automatically load URL when survey complete?"),
                'options'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "N"=>gT("No",'unescaped'),
                ),
                'current'=>$esrow['autoredirect'],
            ),
            'showxquestions'=>array(
                'type'=>'select',
                'label'=>gT('Show "There are X questions in this survey"'),
                'options'=>$aShowxquestionsOptions,
                'current'=>$sValShowxquestions,
                'htmlOptions'=>array(
                    'readonly'=>$bDisableShowxquestions,
                )
            ),
            'showgroupinfo'=>array(
                'type'=>'select',
                'label'=>gT('Show group name and/or group description'),
                'options'=>$aShowgroupinfoOptions,
                'current'=>$sValShowgroupinfo,
                'htmlOptions'=>array(
                    'readonly'=>$bDisableShowgroupinfo,
                )
            ),
            'showqnumcode'=>array(
                'type'=>'select',
                'label'=>gT('Show question number and/or code'),
                'options'=>$aShowqnumcodeOptions,
                'current'=>$sValShowqnumcode,
                'htmlOptions'=>array(
                    'readonly'=>$bDisableShowqnumcode,
                )
            ),
            'shownoanswer'=>array(
                'type'=>'select',
                'label'=>gT('Show "No answer"'),
                'options'=>$aShownoanswerOptions,
                'current'=>$sValShownoanswer,
                'htmlOptions'=>array(
                    'readonly'=>$bDisableShownoanswer,
                )
            ),
        ),
    ));
?>
