<div class='header ui-widget-header'><?php eT("Bounce settings"); ?></div>
<div id='bouncesettingsdiv'>
<?php
    /* Script for disable some setting */
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "tokenbounce.js");
    App()->getClientScript()->registerScript('bounceSettings',"hideShowParameters();",CClientScript::POS_END);

    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'id'=>'bouncesettings',
        //'title'=>gt("Bounce settings"),
        'action' => array('admin/tokens', 'sa'=>'bouncesettings','surveyid'=>$surveyid),
        'formHtmlOptions'=>array(
            'class'=>'form-core',
        ),
        'inlist'=>true,
        'settings' => array(
            'bounce_email'=>array(
                'type'=>'email',
                'label'=>gT('Survey bounce email'),
                'htmlOptions'=>array(
                    'size'=>50,
                ),
                'current'=>$settings['bounce_email'],
            ),
            'bounceprocessing'=>array(
                'type'=>'select',
                'options'=>array(
                    'N'=>gT("None"),
                    'L'=>gT("Use settings below"),
                    'G'=>gT("Use global settings"),
                ),
                'events'=>array(
                    'change'=>'js: function(e) { hideShowParameters(); }',
                ),
                'label'=>gT('Bounce settings to be used'),
                'current'=>$settings['bounceprocessing'],
            ),
            'bounceaccounttype'=>array(
                'type'=>'select',
                'options'=>array(
                    'IMAP'=>gT("IMAP"),
                    'POP'=>gT("POP"),
                ),
                'label'=>gT('Server type'),
                'current'=>$settings['bounceaccounttype'],
            ),
            'bounceaccounthost'=>array(
                'type'=>'string',
                'label'=>gT('Server name & port'),
                'htmlOptions'=>array(
                    'size'=>50,
                ),
                'current'=>$settings['bounceaccounthost'],
            ),
            'bounceaccountuser'=>array(
                'type'=>'string',
                'label'=>gT('User name'),
                'htmlOptions'=>array(
                    'size'=>50,
                ),
                'current'=>$settings['bounceaccountuser'],
            ),
            'bounceaccountpass'=>array(
                'type'=>'password',
                'label'=>gT('Password'),
                'htmlOptions'=>array(
                    'size'=>50,
                ),
                'current'=>$settings['bounceaccountpass'],
            ),
            'bounceaccountencryption'=>array(
                'type'=>'select',
                'options'=>array(
                    'Off'=>gT("None"),
                    'SSL'=>gT("SSL"),
                    'TLS'=>gT("TLS"),
                ),
                'label'=>gT('Encryption type'),
                'current'=>$settings['bounceaccountencryption'],
                'default'=>'Off',
            ),
        ),
        'buttons' => array(
            gT('Save bounce settings')=>array(
                'type'=>'submit',
                'htmlOptions'=>array(
                    'name'=>'save',
                    'value'=>'save',
                ),
            ),
            gT('Cancel') => array(
                'type' => 'link',
                'href' => App()->createUrl('admin/tokens',array("sa"=>"index","surveyid"=>$surveyid)),
            )
        )
    ));
?>
