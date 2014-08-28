<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>
    </div>
    <div class="span9">
        <?php echo CHtml::beginForm($this->createUrl('installer/database'), 'post', array('class' => 'form-horizontal')); ?>
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <div style="color:red; font-size:12px;">
            <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
        </div>
        <?php $clang->eT("Note: All fields marked with (*) are required."); ?>
        <fieldset>
            <legend><?php $clang->eT("Database configuration"); ?></legend>
            <?php
                $rows = array();
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbtype', array('class' => 'control-label', 'label' => $clang->gT("Database type"))),
                    'control' => CHtml::activeDropDownList($model, 'dbtype', $model->supported_db_types, array('required' => 'required', 'autofocus' => 'autofocus')),
                    'description' => $clang->gT("The type of your database management system")
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dblocation', array('class' => 'control-label', 'label' => $clang->gT("Database location"))),
                    'control' => CHtml::activeTextField($model, 'dblocation', array('required' => 'required')),
                    'description' => $clang->gT('Set this to the IP/net location of your database server. In most cases "localhost" will work. You can force Unix socket with complete socket path.').' '.$clang->gT('If your database is using a custom port attach it using a colon. Example: db.example.com:5431')
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbuser', array('class' => 'control-label', 'label' => $clang->gT("Database user"))),
                    'control' => CHtml::activeTextField($model, 'dbuser', array('required' => 'required')),
                    'description' => $clang->gT('Your database server user name. In most cases "root" will work.')
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbpwd', array('class' => 'control-label', 'label' => $clang->gT("Database password"))),
                    'control' => CHtml::activePasswordField($model, 'dbpwd'),
                    'description' => $clang->gT("Your database server password.")
                );
                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbname', array('class' => 'control-label', 'label' => $clang->gT("Database name"))),
                    'control' => CHtml::activeTextField($model, 'dbname', array('required' => 'required')),
                    'description' => $clang->gT("If the database does not yet exist it will be created (make sure your database user has the necessary permissions). In contrast, if there are existing LimeSurvey tables in that database they will be upgraded automatically after installation.")
                );

                $rows[] = array(
                    'label' => CHtml::activeLabelEx($model, 'dbprefix', array('class' => 'control-label', 'label' => $clang->gT("Table prefix"))),
                    'control' => CHtml::activeTextField($model, 'dbprefix', array('value' => 'lime_')),
                    'description' => $clang->gT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.')
                );

            foreach ($rows as $row)
            {
                echo CHtml::openTag('div', array('class' => 'control-group'));
                    echo $row['label'];
                    echo CHtml::tag('div', array('class' => 'controls'), $row['control'] . CHtml::tag('div', array('class' => 'description-field'), $row['description']));
                echo CHtml::closeTag('div');
            }
            
            ?>
        </fieldset>
        <div class="row">
            <div class="span3" >
                <input class="btn" type="button" value="<?php $clang->eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" />
            </div>
            <div class="span3" style="text-align: center;">
            </div>
            <div class="span3" style="text-align: right;">
                <?php echo CHtml::submitButton($clang->gT("Next"), array('class' => 'btn')); ?>
            </div>
        </div>
        <?php echo CHtml::endForm(); ?>

    </div>
</div>


