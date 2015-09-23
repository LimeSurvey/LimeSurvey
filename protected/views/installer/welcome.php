<?php echo CHtml::beginForm('', 'post', [
    'class' => 'form-horizontal'
]); ?>
    <div class="form-group">
        <?php
            echo CHtml::label(gT('Please select your preferred language:'), 'installerLang', [
                'class' => 'control-label col-md-6'
            ]);
            
            echo CHtml::tag('div', ['class' => 'col-md-6'], CHtml::dropDownList('installerLang', 'en', $languages, [
                'id' => 'installerLang', 
                'encode' => false,
                'class' => 'form-control'
            ]));
            echo CHtml::tag('span', array('class' => 'help-block'), gT('Your preferred language will be used through out the installation process.'));
        ?>
    </div>
    <div class="btn-group pull-right">
        <?php echo TbHtml::submitButton(gT('Start installation'), ['color' => TbHtml::BUTTON_COLOR_PRIMARY]); ?>
    </div>
<?php echo CHtml::endForm(); ?>
