<?php
    // Get the array language
    $aAvailableLang=getLanguageDataRestricted (false, Yii::app()->session['adminlang']);
    unset($aAvailableLang[$esrow['language']]);
    $aLang=array();
    foreach ($aAvailableLang as $lang => $aLanguage) {
        $aLang[$lang]=html_entity_decode($aLanguage['description'], ENT_QUOTES, 'UTF-8')." (".html_entity_decode($aLanguage['nativedescription'], ENT_QUOTES, 'UTF-8').")";
    }
    foreach(Survey::model()->findByPk($surveyid)->additionalLanguages as $sSurveyLang)
    {
        if(!array_key_exists($sSurveyLang,$aLang))
        {
            $aLangInfo=getLanguageNameFromCode($sSurveyLang);
            $aLang[$sSurveyLang]=html_entity_decode($aLangInfo[0], ENT_QUOTES, 'UTF-8')." (".html_entity_decode($aLangInfo[1], ENT_QUOTES, 'UTF-8').")";
        }
    }
    $settings=array(
        //~ 'baselanguage'=>array(
            //~ 'type'=>'info',
            //~ 'label'=>gT('Base language'),
            //~ 'content'=>getLanguageNameFromCode($esrow['language'],false), // Or show a select readonly/deactivated mode ?
        //~ ),
        /* Alternate solution for base lang */
        'baselanguage'=>array(
            'type'=>'select',
            'label'=>gT('Base language'),
            'options'=>array(
                $esrow['language']=html_entity_decode(getLanguageNameFromCode($esrow['language'],false), ENT_QUOTES, 'UTF-8'),
            ),
            'htmlOptions'=>array(
                'disabled'=>true,
            ),
            'current'=>$esrow['language'],
        ),
        'additional_languages'=>array(
            'type'=>'select',
            'label'=>gT('Additional Languages'),
            'htmlOptions'=>array(
                'multiple'=>true,
            ),
            'options'=>$aLang,
            'current'=>Survey::model()->findByPk($surveyid)->additionalLanguages,
            'help'=>gT("If you remove a language, all questions, answers, etc for removed languages will be lost."),
            'events'=>array(
                'change'=>'js: function(e) { }',// TODO : a function to validate removing of language
            ),
        ),
        'admin'=>array(
            'type'=>'string',
            'label'=>gT("Administrator"),
            'value'=>$esrow['admin'],
            'htmlOptions'=>array(
                'size'=>50,
            ),
            'current'=>$esrow['admin'],
        ),
        'adminemail'=>array(
            'type'=>'email',
            'label'=>gT("Admin email"),
            'value'=>$esrow['adminemail'],
            'htmlOptions'=>array(
                'size'=>50,
            ),
            'current'=>$esrow['adminemail'],
        ),
        'bounce_email'=>array(
            'type'=>'email',
            'label'=>gT("Bounce email"),
            'value'=>$esrow['bounce_email'],
            'htmlOptions'=>array(
                'size'=>50,
            ),
            'current'=>$esrow['bounce_email'],
        ),
        'faxto'=>array(
            'type'=>'string',
            'label'=>gT("Fax to"),
            'value'=>$esrow['faxto'],
            'htmlOptions'=>array(
                'size'=>50,
            ),
            'current'=>$esrow['faxto'],
        ),
    );
    if(!count($aLang))
        unset($settings['additional_languages']);
    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'id'=>'general',
        'title'=>gt("General"),
        'form' => false,
        'formHtmlOptions'=>array(
            'class'=>'form-core',
        ),
        'settings' => $settings,
    ));
?>
