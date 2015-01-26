<?php
    // Get the array language
    $aAvailableLang=getLanguageDataRestricted (false, Yii::app()->session['adminlang']);
    unset($aAvailableLang[$esrow['language']]);
    $aLang=array();
    foreach ($aAvailableLang as $lang => $aLanguage) {
        $aLang[$lang]="{$aLanguage['description']} (".html_entity_decode($aLanguage['nativedescription'], ENT_NOQUOTES, 'UTF-8').")";
    }

    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'id'=>'general',
        //'title'=>gt("General"),
        'form' => false,
        'formHtmlOptions'=>array(
            'class'=>'form-core',
        ),
        'inlist'=>true,
        'settings' => array(
#            'baselanguage'=>array(
#                'type'=>'info',
#                'label'=>gT('Base language'),
#                'content'=>getLanguageNameFromCode($esrow['language'],false), // Or show a select readonly/deactivated mode ?
#            ),
            /* Alternate solution for base lang */
            'baselanguage'=>array(
                'type'=>'select',
                'label'=>gT('Base language'),
                'options'=>array(
                    $esrow['language']=getLanguageNameFromCode($esrow['language'],false),
                ),
                'htmlOptions'=>array(
                    'disabled'=>true,
                ),
                'selectOptions'=>array(
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
        ),
    ));
?>
