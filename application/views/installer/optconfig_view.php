<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>
    </div>
    <div class="span9">
    <?php echo CHtml::beginForm($this->createUrl('installer/optional'), 'post', array('class' => 'form-horizontal')); ?>
    <h2><?php echo $title; ?></h2>
    <p><?php echo $descp; ?></p>
    <?php echo $confirmation; ?>
    <div style="color:red; font-size:12px;">
        <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
    </div>
    <?php  ?>
    <fieldset>
    <legend><?php 
        $clang->eT("You can leave these settings blank and change them later");
        ?>

    </legend>
    <?php
        $rows = array();
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminLoginName', array('class' => 'control-label', 'label' => $clang->gT("Admin login name"), 'autofocus' => 'autofocus')),
            'description' => $clang->gT("This will be the userid by which admin of board will login."),
            'control' => CHtml::activeTextField($model, 'adminLoginName')
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminLoginPwd', array('class' => 'control-label', 'label' => $clang->gT("Admin login password"))),
            'description' => $clang->gT("This will be the password of admin user."),
            'control' => CHtml::activePasswordField($model, 'adminLoginPwd')
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'confirmPwd', array('class' => 'control-label', 'label' => $clang->gT("Confirm your admin password"))),
            'control' => CHtml::activePasswordField($model, 'confirmPwd')
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminName', array('class' => 'control-label', 'label' => $clang->gT("Administrator name"))),
            'description' => $clang->gT("This is the default name of the site administrator and used for system messages and contact options."),
            'control' => CHtml::activeTextField($model, 'adminName')
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminEmail', array('class' => 'control-label', 'label' => $clang->gT("Administrator email"))),
            'description' => $clang->gT("This is the default email address of the site administrator and used for system messages, contact options and default bounce email."),
            'control' => CHtml::activeTextField($model, 'adminEmail')
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'siteName', array('class' => 'control-label', 'label' => $clang->gT("Site name"))),
            'description' => $clang->gT("This name will appear in the survey list overview and in the administration header."),
            'control' => CHtml::activeTextField($model, 'siteName')
        );
        foreach(getLanguageData(true, Yii::app()->session['installerLang']) as $langkey => $languagekind)
        {
            $languages[$langkey] = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
        }

        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'surveylang', array('class' => 'control-label', 'label' => $clang->gT("Default language"))),
            'description' => $clang->gT("This will be your default language."),
            'control' => CHtml::activeDropDownList($model, 'surveylang', $languages, array('style' => 'width: 156px', 'encode' => false, 'options'=>array('en' => array('selected' => true))))
        );

        foreach ($rows as $row)
        {
            echo CHtml::openTag('div', array('class' => 'control-group'));
                echo $row['label'];

                echo CHtml::openTag('div', array('class' => 'controls'));
                echo $row['control'];
                if (isset($row['description']))
                {
                    echo CHtml::tag('div', array('class' => 'description-field'), $row['description']);
                }
                echo CHtml::closeTag('div');
            echo CHtml::closeTag('div');
        }
    ?>
    </fieldset>
        <div class="row navigator">
            <div class="span3">
                <input class="btn" type="button" value="<?php $clang->eT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/welcome"); ?>', '_top')" />
            </div>
            <div class="span3"></div>
            <div class="span3">
                <?php echo CHtml::submitButton($clang->gT("Next"), array('class' => 'btn')); ?>
            </div>
        </div>

    <?php echo CHtml::endForm(); ?>
    </div>
</div>
