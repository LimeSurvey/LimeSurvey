<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-lg-9">
    <h2><?php echo $title; ?></h2>
    <legend><?php echo $descp; ?></legend>
    <?php if (isset($confirmation)) : ?>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $confirmation,
            'type' => 'success',
        ]);
        ?>
    <?php endif; ?>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]);
        ?>
    <?php echo CHtml::beginForm($this->createUrl('installer/optional'), 'post', array('class' => '')); ?>
    <div class='mb-3'>
        <div class='col-12'>
            <i class='ri-information-fill'></i><?php eT("You can leave these settings blank and change them later"); ?>
        </div>
    </div>

    <?php
        $rows = array();
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminLoginName', array('class' => 'form-label ', 'label' => gT("Admin username"), 'autofocus' => 'autofocus')),
            'description' => gT("This will be the userid by which admin of board will login."),
            'control' => CHtml::activeTextField($model, 'adminLoginName', array('class' => 'form-control', 'required' => true))
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminLoginPwd', array('class' => 'form-label ', 'label' => gT("Admin password"))),
            'description' => gT("This will be the password of admin user."),
            'control' => CHtml::activePasswordField($model, 'adminLoginPwd', array('class' => 'form-control', 'required' => true))
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'confirmPwd', array('class' => 'form-label ', 'label' => gT("Confirm your admin password"))),
            'control' => CHtml::activePasswordField($model, 'confirmPwd', array('class' => 'form-control', 'required' => true))
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminName', array('class' => 'form-label ', 'label' => gT("Administrator name"))),
            'description' => gT("This is the default name of the site administrator and used for system messages and contact options."),
            'control' => CHtml::activeTextField($model, 'adminName', array('class' => 'form-control'))
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'adminEmail', array('class' => 'form-label ', 'label' => gT("Administrator email address"))),
            'description' => gT("This is the default email address of the site administrator and used for system messages, contact options and default bounce email."),
            'control' => CHtml::activeEmailField($model, 'adminEmail', array('class' => 'form-control', 'required' => true, 'placeholder' => 'admin@example.org'))
        );
        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'siteName', array('class' => 'form-label ', 'label' => gT("Site name"))),
            'description' => gT("This name will appear in the survey list overview and in the administration header."),
            'control' => CHtml::activeTextField($model, 'siteName', array('class' => 'form-control'))
        );
        foreach(getLanguageData(true, Yii::app()->session['installerLang']) as $langkey => $languagekind)
        {
            $languages[$langkey] = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
        }

        $rows[] = array(
            'label' => CHtml::activeLabelEx($model, 'surveylang', array('class' => 'form-label ', 'label' => gT("Default language"))),
            'description' => gT("This will be your default language."),
            'control' => CHtml::activeDropDownList($model, 'surveylang', $languages, array('style' => '', 'class'=>'form-control', 'encode' => false, 'options'=>array('en' => array('selected' => true))))
        );

        foreach ($rows as $row)
        {
            echo CHtml::openTag('div', array('class' => 'mb-3'));
                echo $row['label'];

                echo CHtml::openTag('div', array('class' => ''));
                echo $row['control'];
                if (isset($row['description']))
                {
                    echo CHtml::tag('div', array('class' => 'help-block'), $row['description']);
                }
                echo CHtml::closeTag('div');
            echo CHtml::closeTag('div');
        }
    ?>
        <div class="row navigator">
            <div class="col-lg-4">
            </div>
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <?php echo CHtml::submitButton(gT("Next",'unescaped'), array('class' => 'btn btn-outline-secondary')); ?>
            </div>
        </div>

    <?php echo CHtml::endForm(); ?>
    </div>
</div>
