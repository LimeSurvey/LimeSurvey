<?php echo TbHtml::beginForm(['installer/optional'], 'post', ['class' => 'form-horizontal']); ?>
<?php echo $confirmation; ?>
<div style="color:red; font-size:12px;">
    <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
</div>
<?php
    $labelClasses = 'control-label col-sm-4';
    $rows = [];
    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'adminLoginName', array('class' => $labelClasses, 'label' => gT("Admin login name"), 'autofocus' => 'autofocus')),
        'description' => gT("This will be the userid by which admin of board will login."),
        'control' => TbHtml::activeTextField($model, 'adminLoginName')
    );
    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'adminLoginPwd', array('class' => $labelClasses, 'label' => gT("Admin login password"))),
        'description' => gT("This will be the password of admin user."),
        'control' => TbHtml::activePasswordField($model, 'adminLoginPwd')
    );
    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'confirmPwd', array('class' => $labelClasses, 'label' => gT("Confirm your admin password"))),
        'control' => TbHtml::activePasswordField($model, 'confirmPwd')
    );
    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'adminName', array('class' => $labelClasses, 'label' => gT("Administrator name"))),
        'description' => gT("This is the default name of the site administrator and used for system messages and contact options."),
        'control' => TbHtml::activeTextField($model, 'adminName')
    );
    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'adminEmail', array('class' => $labelClasses, 'label' => gT("Administrator email"))),
        'description' => gT("This is the default email address of the site administrator and used for system messages, contact options and default bounce email."),
        'control' => TbHtml::activeTextField($model, 'adminEmail')
    );
    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'siteName', array('class' => $labelClasses, 'label' => gT("Site name"))),
        'description' => gT("This name will appear in the survey list overview and in the administration header."),
        'control' => TbHtml::activeTextField($model, 'siteName')
    );
    foreach(getLanguageData(true, Yii::app()->session['installerLang']) as $langkey => $languagekind)
    {
        $languages[$langkey] = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
    }

    $rows[] = array(
        'label' => CHtml::activeLabelEx($model, 'surveylang', array('class' => $labelClasses, 'label' => gT("Default language"))),
        'description' => gT("This will be your default language."),
        'control' => TbHtml::activeDropDownList($model, 'surveylang', $languages, array('style' => 'width: 156px', 'encode' => false, 'options'=>array('en' => array('selected' => true))))
    );

    foreach ($rows as $row)
    {
        echo CHtml::openTag('div', array('class' => 'form-group'));
            echo $row['label'];
            
            echo CHtml::openTag('div', array('class' => 'col-sm-8'));
            echo $row['control'];
//            
//            echo CHtml::closeTag('div');
//            if (isset($row['description']))
//            {
//                echo CHtml::tag('div', array('class' => 'description-field'), $row['description']);
//            }
            echo CHtml::closeTag('div');
        echo CHtml::closeTag('div');
    }
?>
<div class="btn-group pull-right">
    <?php 
        echo TbHtml::linkButton(gT('Restart'), ['url' => ['installer/index']]);
        echo TbHtml::submitButton(gT("Next"), ['color' => TbHtml::BUTTON_COLOR_PRIMARY]); 
    ?>    
</div>

<?php echo CHtml::endForm(); ?>
